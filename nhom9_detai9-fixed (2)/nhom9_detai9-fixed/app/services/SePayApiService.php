<?php
// ============================================================
// File: app/services/SePayApiService.php
// Mục đích: Tự động kiểm tra giao dịch qua SePay API
//
// CÁCH HOẠT ĐỘNG (không cần ngrok):
// ─────────────────────────────────────────────────────────
//  Webhook cũ: SePay gọi VÀO máy bạn  → cần ngrok
//  Cách này:   Máy bạn hỏi RA SePay   → không cần gì thêm!
//
//  Cứ mỗi 5 giây, JS gọi ?action=payment/check
//  → PHP gọi API SePay: "có giao dịch nào khớp không?"
//  → SePay trả về danh sách giao dịch gần nhất
//  → Tìm BOOKING #XXXXXX trong nội dung → confirmed!
// ─────────────────────────────────────────────────────────
// API SePay dùng:
//   GET https://my.sepay.vn/userapi/transactions/list
//   Header: Authorization: Bearer {API_TOKEN}
//   Params: account_number, limit, transaction_date_min
// ============================================================

class SePayApiService
{
    private string $apiToken;
    private string $accountNumber;
    private string $apiUrl = 'https://my.sepay.vn/userapi/transactions/list';

    public function __construct()
    {
        $this->apiToken      = defined('SEPAY_API_TOKEN')  ? SEPAY_API_TOKEN  : '';
        $this->accountNumber = defined('BANK_ACCOUNT')     ? BANK_ACCOUNT     : '';
    }

    // ============================================================
    // KIỂM TRA GIAO DỊCH CHO 1 BOOKING CỤ THỂ
    // Gọi từ PaymentController::checkStatus() mỗi 5 giây
    // ============================================================

    /**
     * Kiểm tra xem booking này đã được thanh toán chưa.
     *
     * Flow:
     * 1. Gọi SePay API lấy 20 giao dịch gần nhất
     * 2. Duyệt từng giao dịch, tìm nội dung có "BOOKING #XXXXXX"
     * 3. Kiểm tra số tiền có khớp không (±1000đ để tránh sai lệch làm tròn)
     * 4. Nếu khớp → trả về true
     *
     * @param int   $bookingId   ID booking cần kiểm tra
     * @param float $amount      Số tiền cần khớp
     * @return bool              true = đã thanh toán
     */
    /**
     * Kiểm tra thanh toán.
     * @param bool $debug  Nếu true, trả về mảng chi tiết thay vì bool
     */
    public function checkPayment(int $bookingId, float $amount, bool $debug = false)
    {
        $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $transactions = $this->fetchTransactions($since);

        $debugInfo = [
            'bookingId'       => $bookingId,
            'expectedAmount'  => $amount,
            'transactionCount'=> count($transactions),
            'transactions'    => [],
            'matched'         => false,
        ];

        if (empty($transactions)) {
            if ($debug) return array_merge($debugInfo, ['reason' => 'no_transactions_returned']);
            return false;
        }

        $pattern = '/BOOKING\s*#?\s*0*' . $bookingId . '\b/i';

        foreach ($transactions as $tx) {
            $content   = $tx['transaction_content'] ?? $tx['description'] ?? '';
            // SePay MB Bank: dùng amount_in cho tiền vào, amount_out cho tiền ra
            // Nếu amount_out > 0 mà amount_in = 0 → đây là GD tiền ra, bỏ qua
            $amountIn  = (float)($tx['amount_in']  ?? 0);
            $amountOut = (float)($tx['amount_out'] ?? 0);
            $txAmount  = $amountIn > 0 ? $amountIn : (float)($tx['amount'] ?? 0);

            if ($debug) {
                $debugInfo['transactions'][] = [
                    'type'      => $tx['transaction_type'] ?? '',
                    'content'   => $content,
                    'amount_in' => $amountIn,
                    'amount_out'=> $amountOut,
                    'amount'    => $txAmount,
                    'raw_keys'  => array_keys($tx),
                ];
            }

            // Bỏ qua giao dịch tiền RA (amount_out > 0, amount_in = 0)
            if ($amountOut > 0 && $amountIn == 0) continue;

            if (!preg_match($pattern, $content)) continue;

            if (abs($txAmount - $amount) > 1000) {
                $this->log('AMOUNT_MISMATCH', [
                    'bookingId' => $bookingId,
                    'expected'  => $amount,
                    'received'  => $txAmount,
                    'content'   => $content,
                ]);
                if ($debug) $debugInfo['amount_mismatch'] = ['expected' => $amount, 'got' => $txAmount];
                continue;
            }

            $this->log('PAYMENT_FOUND', [
                'bookingId'  => $bookingId,
                'amount'     => $txAmount,
                'content'    => $content,
                'txId'       => $tx['id'] ?? '',
                'txDate'     => $tx['transaction_date'] ?? '',
            ]);

            if ($debug) return array_merge($debugInfo, ['matched' => true]);
            return true;
        }

        if ($debug) return array_merge($debugInfo, ['matched' => false]);
        return false;
    }

    // ============================================================
    // GỌI SEPAY API LẤY DANH SÁCH GIAO DỊCH
    // ============================================================

    /**
     * Gọi SePay API lấy giao dịch gần nhất.
     *
     * SePay API endpoint:
     *   GET https://my.sepay.vn/userapi/transactions/list
     *
     * Headers:
     *   Authorization: Bearer {API_TOKEN}
     *
     * Params:
     *   account_number       → lọc theo TK ngân hàng
     *   limit                → số giao dịch tối đa (20 là đủ)
     *   transaction_date_min → chỉ lấy từ thời điểm này trở đi
     *
     * @return array  Mảng giao dịch, mỗi phần tử là 1 giao dịch
     */
    private function fetchTransactions(string $since): array
    {
        if (empty($this->apiToken)) {
            $this->log('ERROR', ['reason' => 'SEPAY_API_TOKEN chưa được cấu hình']);
            return [];
        }

        // Build URL với query params
        $url = $this->apiUrl . '?' . http_build_query([
            'account_number'       => $this->accountNumber,
            'limit'                => 20,
            'transaction_date_min' => $since,
        ]);

        // Gọi API bằng cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Xử lý lỗi mạng
        if ($response === false || !empty($curlError)) {
            $this->log('CURL_ERROR', ['error' => $curlError]);
            return [];
        }

        // Parse JSON
        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['transactions'])) {
            $this->log('API_ERROR', [
                'httpCode' => $httpCode,
                'response' => substr($response, 0, 200),
            ]);
            return [];
        }

        return $data['transactions'] ?? [];
    }

    // ============================================================
    // LOG
    // ============================================================
    private function log(string $level, array $data): void
    {
        if (!defined('LOG_PATH')) return;
        $dir = LOG_PATH;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $line = sprintf(
            "[%s] [%-16s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        file_put_contents($dir . '/sepay_api.log', $line, FILE_APPEND | LOCK_EX);
    }
}
