<?php
// ============================================================
// File: app/controllers/PaymentController.php
// Xử lý toàn bộ luồng thanh toán SePay
//
// LUỒNG:
//   [1] showQR()       → hiển thị trang QR sau khi đặt phòng
//   [2] checkStatus()  → AJAX polling mỗi 5s kiểm tra DB
//   [3] webhook()      → SePay gọi tự động khi nhận tiền thật
//   [4] success()      → trang "Thanh toán thành công"
// ============================================================

require_once ROOT_PATH . '/app/services/BookingService.php';
require_once ROOT_PATH . '/app/services/SePayService.php';
require_once ROOT_PATH . '/app/services/MailService.php';    // ← THÊM: gửi email thanh toán

class PaymentController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    // ============================================================
    // [1] HIỂN THỊ TRANG QR
    // URL: ?action=payment/qr  (redirect từ BookingController::store)
    // ============================================================
    public function showQR(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $bookingId = (int)($_GET['booking_id'] ?? $_SESSION['payment_booking_id'] ?? 0);
        if ($bookingId === 0) {
            header('Location: ' . $this->url('booking')); exit;
        }

        $booking = $this->bookingService->findBookingById($bookingId);
        if (!$booking) {
            header('Location: ' . $this->url('booking')); exit;
        }

        // Nếu đã confirmed rồi (reload lại trang) → thẳng sang success
        if ($booking->getStatus() === 'confirmed') {
            header('Location: ' . $this->url('payment/success') . '&booking_id=' . $bookingId);
            exit;
        }

        // Tạo URL QR + nội dung CK
        $qrUrl   = SePayService::buildQRUrl(
            $booking->getId(),
            $booking->getFullname(),
            $booking->getTotalPrice()
        );
        $content = SePayService::buildContent($booking->getId(), $booking->getFullname());

        $this->render('payment/qr', [
            'booking' => $booking,
            'qrUrl'   => $qrUrl,
            'content' => $content,
        ]);
    }

    // ============================================================
    // [2] AJAX POLLING — frontend gọi mỗi 5 giây
    // URL: ?action=payment/check&booking_id=X  (GET)
    // Trả về JSON: { "status": "pending"|"confirmed", "confirmed": bool }
    //
    // KHÔNG CẦN NGROK:
    // Thay vì chờ SePay gọi vào (webhook), PHP chủ động hỏi
    // SePay API xem có giao dịch khớp không → hoạt động trên
    // mọi localhost mà không cần port forwarding.
    // ============================================================
    public function checkStatus(): void
    {
        header('Content-Type: application/json');

        $bookingId = (int)($_GET['booking_id'] ?? 0);
        if ($bookingId === 0) {
            echo json_encode(['error' => 'Thiếu booking_id']); exit;
        }

        $booking = $this->bookingService->findBookingById($bookingId);
        if (!$booking) {
            echo json_encode(['error' => 'Không tìm thấy booking']); exit;
        }

        // Nếu DB đã confirmed (từ lần poll trước) → trả về ngay
        if ($booking->getStatus() === 'confirmed') {
            echo json_encode([
                'status'      => 'confirmed',
                'confirmed'   => true,
                'redirectUrl' => $this->absoluteUrl('payment/success') . '&booking_id=' . $bookingId,
            ]);
            exit;
        }

        // Hỏi SePay API xem có giao dịch khớp chưa
        require_once ROOT_PATH . '/app/services/SePayApiService.php';
        $sePayApi = new SePayApiService();

        // Nếu có ?debug=1 → trả về chi tiết để debug
        $isDebug = !empty($_GET['debug']);
        if ($isDebug) {
            $debugResult = $sePayApi->checkPayment($bookingId, $booking->getTotalPrice(), true);
            echo json_encode($debugResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $paid = $sePayApi->checkPayment($bookingId, $booking->getTotalPrice());

        if ($paid) {
            $this->bookingService->updateBookingStatus($bookingId, 'confirmed');

            // ── GỬI EMAIL XÁC NHẬN THANH TOÁN ──
            try {
                MailService::sendPaymentConfirmation($booking);
            } catch (\Throwable $e) {
                // Ghi log, không ảnh hưởng response
            }

            echo json_encode([
                'status'      => 'confirmed',
                'confirmed'   => true,
                'redirectUrl' => $this->absoluteUrl('payment/success') . '&booking_id=' . $bookingId,
            ]);
        } else {
            echo json_encode([
                'status'    => 'pending',
                'confirmed' => false,
            ]);
        }
        exit;
    }

    // ============================================================
    // [3] WEBHOOK — SePay gọi tự động khi MB nhận tiền
    // URL: ?action=payment/webhook
    // Method: POST
    // Header: Authorization: Bearer YOUR_TOKEN
    // ============================================================
    public function webhook(): void
    {
        // Chỉ nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['code' => '99', 'message' => 'Method Not Allowed']);
            exit;
        }

        // Gọi SePayService xử lý: verify token → parse JSON → tìm booking ID
        $result = SePayService::handleWebhook();

        if (!$result['success']) {
            // Token sai → trả 401
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['code' => '99', 'message' => $result['error']]);
            exit;
        }

        // Giao dịch không liên quan (tiền ra, sai TK, không có BOOKING #) → OK luôn
        if (!empty($result['skipped'])) {
            header('Content-Type: application/json');
            echo json_encode(['code' => '00', 'message' => 'Acknowledged']);
            exit;
        }

        // Tìm thấy booking → cập nhật status = confirmed
        $bookingId = $result['bookingId'];
        $booking   = $this->bookingService->findBookingById($bookingId);

        if ($booking && $booking->getStatus() === 'pending') {
            $this->bookingService->updateBookingStatus($bookingId, 'confirmed');

            // ── GỬI EMAIL XÁC NHẬN THANH TOÁN (qua webhook) ──
            try {
                MailService::sendPaymentConfirmation($booking);
            } catch (\Throwable $e) {}

            SePayService::log('CONFIRMED', [
                'bookingId' => $bookingId,
                'amount'    => $result['amount'],
                'txRef'     => $result['txRef'],
            ]);
        } elseif ($booking && $booking->getStatus() === 'confirmed') {
            SePayService::log('ALREADY_CONFIRMED', ['bookingId' => $bookingId]);
        }

        // QUAN TRỌNG: Luôn trả 200 + code "00"
        // Nếu không, SePay sẽ RETRY liên tục
        header('Content-Type: application/json');
        echo json_encode(['code' => '00', 'message' => 'Success']);
        exit;
    }

    // ============================================================
    // [4] TRANG THANH TOÁN THÀNH CÔNG
    // URL: ?action=payment/success&booking_id=42
    // Redirect đến đây sau khi polling phát hiện confirmed
    // ============================================================
    public function success(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $bookingId = (int)($_GET['booking_id'] ?? $_SESSION['last_booking_id'] ?? 0);
        if ($bookingId === 0) {
            header('Location: ' . $this->url('home')); exit;
        }

        $booking = $this->bookingService->findBookingById($bookingId);
        if (!$booking) {
            header('Location: ' . $this->url('home')); exit;
        }

        // Dọn session
        unset($_SESSION['last_booking_id'], $_SESSION['payment_booking_id']);

        $this->render('payment/success', ['booking' => $booking]);
    }

    // ---- Helpers ----
    private function render(string $view, array $data = []): void
    {
        extract($data);
        $path = ROOT_PATH . "/app/views/{$view}.php";
        if (!file_exists($path)) die("View not found: $path");
        include $path;
    }

    private function url(string $path): string
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return $base . '/?action=' . ltrim($path, '/');
    }

    private function absoluteUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return $scheme . '://' . $host . $base . '/?action=' . ltrim($path, '/');
    }
}
