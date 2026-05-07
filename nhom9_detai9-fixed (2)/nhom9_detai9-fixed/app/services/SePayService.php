<?php
// ============================================================
// File: app/services/SePayService.php
// Mục đích: Tạo QR VietQR + xử lý webhook THẬT từ SePay
//
// SePay hoạt động thế nào?
// ─────────────────────────────────────────────────────────
//  1. Bạn tạo QR bằng URL img.vietqr.io (free, không cần API key)
//  2. Khách quét QR → chuyển khoản qua app ngân hàng
//  3. MB nhận tiền → SePay phát hiện biến động số dư
//  4. SePay gửi POST webhook về URL bạn đã đăng ký
//  5. Server nhận webhook → verify token → tìm booking → confirm
// ─────────────────────────────────────────────────────────
// JSON SePay gửi về:
// {
//   "id": 123456,
//   "gateway": "MBBank",
//   "transactionDate": "2024-08-10 14:35:22",
//   "accountNumber": "0123456789",
//   "code": null,                   ← nội dung CK nếu dùng SePay code
//   "content": "BOOKING #000003 NGAN KIEU",
//   "transferType": "in",           ← "in" = tiền vào, "out" = tiền ra
//   "transferAmount": 500000,
//   "accumulated": 1500000,
//   "referenceCode": "FT24222123",
//   "description": "BOOKING #000003 NGAN KIEU"
// }
// ============================================================

class SePayService
{
    // --------------------------------------------------
    // TẠO QR CODE
    // --------------------------------------------------

    /**
     * Tạo URL ảnh QR VietQR cho booking.
     *
     * Dùng CDN miễn phí của VietQR — không cần API key.
     * URL pattern: https://img.vietqr.io/image/{bank}-{account}-{template}.png
     *
     * @param int    $bookingId   ID booking
     * @param string $fullname    Tên khách (để tạo nội dung CK)
     * @param float  $amount      Số tiền cần thanh toán
     * @return string             URL ảnh QR
     */
    public static function buildQRUrl(int $bookingId, string $fullname, float $amount): string
    {
        // Nội dung CK: "BOOKING #000003 NGAN KIEU"
        // SePay đọc chính xác chuỗi này để đối soát
        $content = self::buildContent($bookingId, $fullname);

        // Tạo URL QR theo chuẩn VietQR CDN
        return sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            urlencode(BANK_ID),
            urlencode(BANK_ACCOUNT),
            (int) round($amount),
            urlencode($content),
            urlencode(ACCOUNT_NAME)
        );
    }

    /**
     * Tạo nội dung chuyển khoản chuẩn.
     * Format: "BOOKING #000003 NGAN KIEU"
     * SePay dùng chuỗi này để tự động khớp với booking.
     */
    public static function buildContent(int $bookingId, string $fullname): string
    {
        $code      = str_pad($bookingId, 6, '0', STR_PAD_LEFT);
        $nameClean = strtoupper(self::removeAccents($fullname));
        $nameClean = substr($nameClean, 0, 30); // giới hạn độ dài
        return "BOOKING #{$code} {$nameClean}";
    }

    // --------------------------------------------------
    // XỬ LÝ WEBHOOK THẬT TỪ SEPAY
    // --------------------------------------------------

    /**
     * Nhận và xử lý webhook POST từ SePay.
     *
     * SePay xác thực bằng Bearer token trong header Authorization:
     *   Authorization: Bearer YOUR_SEPAY_WEBHOOK_TOKEN
     *
     * @return array  ['success', 'bookingId'?, 'amount'?, 'skipped'?, 'error'?]
     */
    public static function handleWebhook(): array
    {
        // Ghi log ngay lập tức — dù có lỗi hay không
        $rawBody = file_get_contents('php://input');
        self::log('RECEIVED', [
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
            'authorization' => substr($_SERVER['HTTP_AUTHORIZATION'] ?? '', 0, 30) . '...',
            'body'          => $rawBody,
        ]);

        // ── BƯỚC 1: Xác minh Bearer token ──
        // SePay gửi: Authorization: Bearer TOKEN
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                   ?? apache_request_headers()['Authorization']
                   ?? apache_request_headers()['authorization']
                   ?? '';

        $token = str_replace('Bearer ', '', $authHeader);

        if ($token !== SEPAY_WEBHOOK_TOKEN) {
            self::log('ERROR', ['reason' => 'Token không khớp', 'received' => substr($token, 0, 10)]);
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        // ── BƯỚC 2: Parse JSON ──
        $data = json_decode($rawBody, true);
        if (!$data) {
            self::log('ERROR', ['reason' => 'JSON không hợp lệ', 'raw' => $rawBody]);
            return ['success' => false, 'error' => 'Invalid JSON'];
        }

        // ── BƯỚC 3: Chỉ xử lý giao dịch tiền VÀO ──
        $transferType = $data['transferType'] ?? '';
        if ($transferType !== 'in') {
            self::log('SKIP', ['reason' => 'Không phải tiền vào', 'type' => $transferType]);
            return ['success' => true, 'skipped' => true];
        }

        // ── BƯỚC 4: Kiểm tra đúng tài khoản ──
        $accountNumber = $data['accountNumber'] ?? '';
        if ($accountNumber && $accountNumber !== BANK_ACCOUNT) {
            self::log('SKIP', ['reason' => 'Sai tài khoản', 'account' => $accountNumber]);
            return ['success' => true, 'skipped' => true];
        }

        // ── BƯỚC 5: Tìm booking ID trong nội dung CK ──
        // SePay gửi nội dung CK trong field 'content' hoặc 'description'
        $content   = $data['content'] ?? $data['description'] ?? '';
        $bookingId = self::extractBookingId($content);

        if (!$bookingId) {
            self::log('SKIP', [
                'reason'  => 'Không tìm thấy BOOKING # trong nội dung',
                'content' => $content,
            ]);
            return ['success' => true, 'skipped' => true, 'reason' => 'No booking ID'];
        }

        $amount = (float)($data['transferAmount'] ?? 0);

        self::log('MATCHED', [
            'bookingId' => $bookingId,
            'amount'    => $amount,
            'content'   => $content,
            'txRef'     => $data['referenceCode'] ?? '',
        ]);

        return [
            'success'   => true,
            'bookingId' => $bookingId,
            'amount'    => $amount,
            'txRef'     => $data['referenceCode'] ?? '',
        ];
    }

    // --------------------------------------------------
    // HELPERS
    // --------------------------------------------------

    /**
     * Trích xuất booking ID từ nội dung chuyển khoản.
     * "BOOKING #000042 NGUYEN VAN AN" → 42
     */
    private static function extractBookingId(string $content): ?int
    {
        if (preg_match('/BOOKING\s*#\s*0*(\d+)/i', $content, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    /** Bỏ dấu tiếng Việt → ASCII (chuẩn NAPAS) */
    private static function removeAccents(string $str): string
    {
        $from = ['à','á','â','ã','è','é','ê','ì','í','ò','ó','ô','õ','ù','ú','ý',
                 'À','Á','Â','Ã','È','É','Ê','Ì','Í','Ò','Ó','Ô','Õ','Ù','Ú','Ý',
                 'ă','ắ','ặ','ằ','ẳ','ẵ','Ă','Ắ','Ặ','Ằ','Ẳ','Ẵ',
                 'đ','Đ','ơ','ớ','ợ','ờ','ở','ỡ','Ơ','Ớ','Ợ','Ờ','Ở','Ỡ',
                 'ư','ứ','ự','ừ','ử','ữ','Ư','Ứ','Ự','Ừ','Ử','Ữ',
                 'ạ','ả','ấ','ầ','ẩ','ẫ','ậ','Ạ','Ả','Ấ','Ầ','Ẩ','Ẫ','Ậ',
                 'ẹ','ẻ','ẽ','ế','ề','ể','ễ','ệ','Ẹ','Ẻ','Ẽ','Ế','Ề','Ể','Ễ','Ệ',
                 'ị','ỉ','Ị','Ỉ','ọ','ỏ','ố','ồ','ổ','ỗ','ộ','Ọ','Ỏ','Ố','Ồ','Ổ','Ỗ','Ộ',
                 'ụ','ủ','Ụ','Ủ','ỳ','ỵ','ỷ','ỹ','Ỳ','Ỵ','Ỷ','Ỹ'];
        $to   = ['a','a','a','a','e','e','e','i','i','o','o','o','o','u','u','y',
                 'A','A','A','A','E','E','E','I','I','O','O','O','O','U','U','Y',
                 'a','a','a','a','a','a','A','A','A','A','A','A',
                 'd','D','o','o','o','o','o','o','O','O','O','O','O','O',
                 'u','u','u','u','u','u','U','U','U','U','U','U',
                 'a','a','a','a','a','a','a','A','A','A','A','A','A','A',
                 'e','e','e','e','e','e','e','e','E','E','E','E','E','E','E','E',
                 'i','i','I','I','o','o','o','o','o','o','o','O','O','O','O','O','O','O',
                 'u','u','U','U','y','y','y','y','Y','Y','Y','Y'];
        return str_replace($from, $to, $str);
    }

    /** Ghi log vào storage/logs/sepay_webhook.log */
    public static function log(string $level, array $data): void
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
        file_put_contents($dir . '/sepay_webhook.log', $line, FILE_APPEND | LOCK_EX);
    }
}
