<?php
// =============================================
// File: app/services/MailService.php
// Gửi email tự động bằng PHPMailer qua Gmail SMTP
// =============================================

require_once ROOT_PATH . '/core/PHPMailer/Exception.php';
require_once ROOT_PATH . '/core/PHPMailer/PHPMailer.php';
require_once ROOT_PATH . '/core/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * Tạo PHPMailer đã cấu hình sẵn SMTP.
     */
    private static function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        return $mail;
    }

    /**
     * Gửi email xác nhận đặt phòng cho khách.
     */
    public static function sendBookingConfirmation(Booking $booking): bool
    {
        try {
            $mail = self::createMailer();
            $mail->addAddress($booking->getEmail(), $booking->getFullname());

            $bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
            $mail->Subject = "[LuxStay] Xác nhận đặt phòng #{$bookingCode}";
            $mail->Body = self::buildBookingEmailBody($booking, $bookingCode);

            $mail->send();

            self::log('BOOKING_MAIL', [
                'to' => $booking->getEmail(),
                'bookingId' => $booking->getId(),
                'status' => 'sent',
            ]);
            return true;

        } catch (Exception $e) {
            self::log('MAIL_ERROR', [
                'type' => 'booking_confirmation',
                'to' => $booking->getEmail(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Gửi email xác nhận thanh toán thành công.
     */
    public static function sendPaymentConfirmation(Booking $booking): bool
    {
        try {
            $mail = self::createMailer();
            $mail->addAddress($booking->getEmail(), $booking->getFullname());

            $bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
            $mail->Subject = "[LuxStay] Thanh toán thành công #{$bookingCode}";
            $mail->Body = self::buildPaymentEmailBody($booking, $bookingCode);

            $mail->send();

            self::log('PAYMENT_MAIL', [
                'to' => $booking->getEmail(),
                'bookingId' => $booking->getId(),
                'status' => 'sent',
            ]);
            return true;

        } catch (Exception $e) {
            self::log('MAIL_ERROR', [
                'type' => 'payment_confirmation',
                'to' => $booking->getEmail(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Gửi email từ form liên hệ (cho admin + auto-reply cho khách).
     *
     * Trả về mảng kết quả chi tiết:
     *   ['admin' => bool, 'reply' => bool, 'error' => string|null]
     * - admin: email gửi tới admin thành công hay không
     * - reply: auto-reply cho khách thành công hay không
     * - error: thông báo lỗi nếu có
     */
    public static function sendContactForm(string $name, string $email, string $message): array
    {
        $result = ['admin' => false, 'reply' => false, 'error' => null];

        // Email 1: Gửi cho admin — try/catch riêng
        try {
            $mail = self::createMailer();
            $mail->addAddress(MAIL_ADMIN);
            $mail->addReplyTo($email, $name);
            $mail->Subject = "[LuxStay] Liên hệ từ " . $name;
            $mail->Body = self::buildContactEmailBody($name, $email, $message);
            $mail->send();
            $result['admin'] = true;
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            self::log('MAIL_ERROR', [
                'type'  => 'contact_to_admin',
                'from'  => $email,
                'error' => $e->getMessage(),
            ]);
        }

        // Email 2: Auto-reply cho khách — try/catch riêng
        // Chỉ gửi nếu email admin đã gửi thành công
        if ($result['admin']) {
            try {
                $reply = self::createMailer();
                $reply->addAddress($email, $name);
                $reply->Subject = "[LuxStay] Cảm ơn bạn đã liên hệ!";
                $reply->Body = self::buildAutoReplyBody($name);
                $reply->send();
                $result['reply'] = true;
            } catch (Exception $e) {
                self::log('MAIL_ERROR', [
                    'type'  => 'contact_auto_reply',
                    'to'    => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log tổng hợp
        self::log('CONTACT_MAIL', [
            'from'       => $email,
            'name'       => $name,
            'admin_sent' => $result['admin'],
            'reply_sent' => $result['reply'],
        ]);

        return $result;
    }

    // ── EMAIL TEMPLATES (HTML) ──

    private static function buildBookingEmailBody(Booking $booking, string $code): string
    {
        $roomNumber = htmlspecialchars($booking->getRoom()->getRoomNumber());
        $roomType = htmlspecialchars($booking->getRoom()->getType());
        $fullname = htmlspecialchars($booking->getFullname());
        $checkIn = $booking->getCheckIn()->format('d/m/Y');
        $checkOut = $booking->getCheckOut()->format('d/m/Y');
        $nights = $booking->getNights();
        $guests = $booking->getGuests();
        $total = number_format($booking->getTotalPrice(), 0, ',', '.');
        $phone = htmlspecialchars($booking->getPhone());

        return "
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto; background:#fff;'>
            <div style='background:linear-gradient(135deg,#b8860b,#d4a843); padding:24px; text-align:center; border-radius:8px 8px 0 0;'>
                <h1 style='color:#fff; margin:0; font-size:22px;'>LuxStay Hotel</h1>
                <p style='color:#fff; margin:8px 0 0; font-size:14px;'>Xác nhận đặt phòng</p>
            </div>

            <div style='padding:24px; border:1px solid #e0e0e0; border-top:none;'>
                <p style='font-size:15px;'>Xin chào <strong>{$fullname}</strong>,</p>
                <p>Cảm ơn bạn đã đặt phòng tại LuxStay Hotel! Dưới đây là thông tin đặt phòng của bạn:</p>

                <div style='background:#fff8e1; border-left:4px solid #b8860b; padding:12px 16px; margin:16px 0; border-radius:4px;'>
                    <strong>Mã đặt phòng: #{$code}</strong>
                </div>

                <table style='width:100%; border-collapse:collapse; margin:16px 0;'>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'><strong>{$roomNumber} — {$roomType}</strong></td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Nhận phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$checkIn}</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Trả phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$checkOut}</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Số đêm</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$nights} đêm</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Số khách</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$guests} khách</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Điện thoại</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$phone}</td></tr>
                    <tr><td style='padding:8px; color:#888;'><strong>Tổng tiền</strong></td>
                        <td style='padding:8px; text-align:right; color:#e65100; font-size:18px;'><strong>{$total}đ</strong></td></tr>
                </table>

                <div style='background:#fff3e0; padding:12px; border-radius:6px; text-align:center;'>
                    <span style='color:#e65100; font-weight:bold;'>Trạng thái: Đang chờ xác nhận</span>
                </div>

                <p style='margin-top:20px; font-size:13px; color:#888;'>
                    Nếu có thắc mắc, vui lòng liên hệ: 0236 300 0000 hoặc reply email này.
                </p>
            </div>

            <div style='background:#f5f5f5; padding:16px; text-align:center; font-size:12px; color:#aaa; border-radius:0 0 8px 8px;'>
                © LuxStay Hotel — 246 Minh Khai, Hai Bà Trưng, Hà Nội
            </div>
        </div>";
    }

    private static function buildPaymentEmailBody(Booking $booking, string $code): string
    {
        $fullname = htmlspecialchars($booking->getFullname());
        $roomNumber = htmlspecialchars($booking->getRoom()->getRoomNumber());
        $roomType = htmlspecialchars($booking->getRoom()->getType());
        $checkIn = $booking->getCheckIn()->format('d/m/Y');
        $checkOut = $booking->getCheckOut()->format('d/m/Y');
        $total = number_format($booking->getTotalPrice(), 0, ',', '.');

        return "
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto; background:#fff;'>
            <div style='background:linear-gradient(135deg,#2e7d32,#43a047); padding:24px; text-align:center; border-radius:8px 8px 0 0;'>
                <h1 style='color:#fff; margin:0; font-size:22px;'>Thanh Toán Thành Công!</h1>
                <p style='color:#fff; margin:8px 0 0; font-size:14px;'>LuxStay Hotel</p>
            </div>

            <div style='padding:24px; border:1px solid #e0e0e0; border-top:none;'>
                <p>Xin chào <strong>{$fullname}</strong>,</p>
                <p>Chúng tôi đã nhận được thanh toán của bạn. Đặt phòng đã được <strong style='color:#2e7d32;'>xác nhận</strong>!</p>

                <div style='background:#e8f5e9; border-left:4px solid #2e7d32; padding:12px 16px; margin:16px 0; border-radius:4px;'>
                    <strong>Mã đặt phòng: #{$code}</strong> — Đã xác nhận
                </div>

                <table style='width:100%; border-collapse:collapse; margin:16px 0;'>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'><strong>{$roomNumber} — {$roomType}</strong></td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Nhận phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$checkIn}</td></tr>
                    <tr><td style='padding:8px; border-bottom:1px solid #f0f0f0; color:#888;'>Trả phòng</td>
                        <td style='padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;'>{$checkOut}</td></tr>
                    <tr><td style='padding:8px; color:#888;'>Đã thanh toán</td>
                        <td style='padding:8px; text-align:right; color:#2e7d32; font-size:18px;'><strong>{$total}đ</strong></td></tr>
                </table>

                <p style='font-size:13px; color:#888;'>Phương thức: VietQR — MB Bank</p>
            </div>

            <div style='background:#f5f5f5; padding:16px; text-align:center; font-size:12px; color:#aaa; border-radius:0 0 8px 8px;'>
                © LuxStay Hotel — 246 Minh Khai, Hai Bà Trưng, Hà Nội
            </div>
        </div>";
    }

    private static function buildContactEmailBody(string $name, string $email, string $message): string
    {
        $name = htmlspecialchars($name);
        $email = htmlspecialchars($email);
        $message = nl2br(htmlspecialchars($message));
        $time = date('d/m/Y H:i:s');

        return "
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto;'>
            <div style='background:#b8860b; padding:16px; text-align:center; border-radius:8px 8px 0 0;'>
                <h2 style='color:#fff; margin:0;'>Liên hệ mới từ website</h2>
            </div>
            <div style='padding:20px; border:1px solid #e0e0e0; border-top:none;'>
                <p><strong>Họ tên:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Thời gian:</strong> {$time}</p>
                <hr style='border:none; border-top:1px solid #eee;'>
                <p><strong>Nội dung:</strong></p>
                <div style='background:#f9f9f9; padding:12px; border-radius:6px;'>{$message}</div>
            </div>
        </div>";
    }

    private static function buildAutoReplyBody(string $name): string
    {
        $name = htmlspecialchars($name);

        return "
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto;'>
            <div style='background:linear-gradient(135deg,#b8860b,#d4a843); padding:20px; text-align:center; border-radius:8px 8px 0 0;'>
                <h2 style='color:#fff; margin:0;'>LuxStay Hotel</h2>
            </div>
            <div style='padding:24px; border:1px solid #e0e0e0; border-top:none;'>
                <p>Xin chào <strong>{$name}</strong>,</p>
                <p>Cảm ơn bạn đã liên hệ với LuxStay Hotel! Chúng tôi đã nhận được tin nhắn của bạn
                   và sẽ phản hồi trong thời gian sớm nhất.</p>
                <p style='color:#888; font-size:13px;'>Đây là email tự động, vui lòng không reply.</p>
            </div>
            <div style='background:#f5f5f5; padding:12px; text-align:center; font-size:12px; color:#aaa; border-radius:0 0 8px 8px;'>
                © LuxStay Hotel — 0236 300 0000
            </div>
        </div>";
    }

    // ── LOG ──

    private static function log(string $level, array $data): void
    {
        if (!defined('LOG_PATH'))
            return;
        $dir = LOG_PATH;
        if (!is_dir($dir))
            mkdir($dir, 0755, true);

        $line = sprintf(
            "[%s] [%-16s] %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
    }
}
