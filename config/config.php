<?php
// =============================================
// File: config/config.php
// =============================================

// ── DATABASE ──────────────────────────────────
// Mỗi người chỉ cần đổi DB_USER / DB_PASS nếu khác root
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');      // port mặc định MySQL — đổi lại 3307 nếu dùng Docker
define('DB_NAME', 'hotel_booking');
define('DB_USER', 'root');
define('DB_PASS', '');

// ── SEPAY & TÀI KHOẢN NGÂN HÀNG ──────────────
// Dùng chung 1 TK — tiền về TK của chủ project
define('SEPAY_API_TOKEN', 'NIRLYC9ETCPUB7OR4NPY4XKFW3DJ1ICWAEJSYCAMASUPVOJ5AIM8QNGW85X3ZHSZ');
define('BANK_ID', 'MB');
define('BANK_ACCOUNT', '9410102005');
define('ACCOUNT_NAME', 'NGUYEN THI KIEU NGAN');

// ── EMAIL (PHPMailer + Gmail SMTP) ────────────
// Dùng Gmail SMTP để gửi email xác nhận đặt phòng
//  MAIL_PASSWORD phải là App Password (16 ký tự), KHÔNG phải mật khẩu Gmail thường
//    Cách tạo: Google Account → Bảo mật → Bật xác minh 2 bước, 
define('MAIL_HOST', 'smtp.gmail.com');   // Server SMTP của Gmail
define('MAIL_PORT', 587);                // Cổng TLS (bảo mật)
define('MAIL_USERNAME', 'phuct7708@gmail.com');          // Email đăng nhập SMTP
define('MAIL_PASSWORD', 'oxww idvj yqbw oxmi');          // ← ĐỔI: App Password Gmail
define('MAIL_FROM_NAME', 'LuxStay Hotel');                // Tên hiển thị trong inbox
define('MAIL_FROM_EMAIL', 'phuct7708@gmail.com');          // Email người gửi
define('MAIL_ADMIN', 'thiephuc.ba@gmail.com');        // Email admin nhận liên hệ

// ── ROOT PATH (không sửa) ─────────────────────
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
define('LOG_PATH', ROOT_PATH . '/storage/logs');
