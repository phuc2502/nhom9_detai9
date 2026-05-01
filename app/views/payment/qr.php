<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<?php
$bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
$amountFmt   = number_format($booking->getTotalPrice(), 0, ',', '.');
?>

<main>
<section class="section-rooms" style="padding:40px 0;">
<div class="container">

    <div class="section-header" style="margin-bottom:32px;">
        <h2 style="font-size:26px;">💳 Thanh Toán VietQR</h2>
        <p style="color:#666; margin-top:6px;">Quét mã QR bằng app ngân hàng để thanh toán</p>
    </div>

    <div style="display:flex; gap:28px; flex-wrap:wrap; justify-content:center; align-items:flex-start;">

        <!-- ══════════════════════════════════
             CỘT TRÁI: QR CODE
        ══════════════════════════════════ -->
        <div style="flex:0 0 320px;">
            <div class="room-card" style="text-align:center; padding:28px 24px;">

                <!-- Badge trạng thái chờ -->
                <div id="badge-pending" style="display:inline-flex; align-items:center; gap:8px;
                     background:#fff8e1; border:1px solid #ffe082; color:#e65100;
                     padding:8px 16px; border-radius:20px; font-weight:600;
                     font-size:13px; margin-bottom:20px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%;
                                 background:#e65100; animation:blink 1.2s infinite;"></span>
                    Đang chờ thanh toán &nbsp;|&nbsp;
                    <span id="timer" style="font-family:monospace; font-size:14px;">15:00</span>
                </div>

                <!-- Ảnh QR từ VietQR CDN -->
                <div style="position:relative; display:inline-block;">
                    <img id="qr-img"
                         src="<?= htmlspecialchars($qrUrl) ?>"
                         alt="QR Thanh toán"
                         style="width:256px; height:256px; border-radius:12px;
                                border:3px solid #e0e0e0; display:block;"
                         onerror="document.getElementById('qr-error').style.display='flex'; this.style.display='none';">

                    <!-- Overlay thành công (ẩn lúc đầu) -->
                    <div id="qr-success-overlay"
                         style="display:none; position:absolute; inset:0; background:rgba(27,94,32,.88);
                                border-radius:12px; align-items:center; justify-content:center;
                                flex-direction:column; color:#fff;">
                        <div style="font-size:52px;">✅</div>
                        <div style="font-weight:700; font-size:16px; margin-top:6px;">Đã thanh toán!</div>
                    </div>

                    <!-- Fallback nếu không load được QR -->
                    <div id="qr-error"
                         style="display:none; width:256px; height:256px; border:2px dashed #ccc;
                                border-radius:12px; align-items:center; justify-content:center;
                                flex-direction:column; color:#aaa; font-size:13px; text-align:center; padding:20px;">
                        ⚠️ Không tải được QR<br>
                        <a href="<?= htmlspecialchars($qrUrl) ?>" target="_blank"
                           style="color:#009688; margin-top:8px; display:block;">Mở QR mới →</a>
                    </div>
                </div>

                <!-- Số tiền + mã booking -->
                <div style="margin-top:20px; font-size:30px; font-weight:800; color:#e65100;">
                    <?= $amountFmt ?>đ
                </div>
                <div style="font-size:13px; color:#888; margin-top:4px;">
                    Mã đặt phòng: <strong style="color:#333;">#<?= $bookingCode ?></strong>
                </div>

                <!-- Nội dung chuyển khoản -->
                <div style="margin-top:16px; background:#f5f5f5; border-radius:8px; padding:12px 14px; text-align:left;">
                    <div style="font-size:11px; color:#999; margin-bottom:4px;">Nội dung chuyển khoản (gõ đúng):</div>
                    <div style="font-size:14px; font-weight:700; color:#1a1a1a; letter-spacing:0.3px;
                                display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <span id="content-text"><?= htmlspecialchars($content) ?></span>
                        <button onclick="copyContent()" title="Sao chép"
                                style="background:none; border:1px solid #ccc; border-radius:6px;
                                       padding:4px 8px; cursor:pointer; font-size:12px; flex-shrink:0;">
                            📋
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ══════════════════════════════════
             CỘT PHẢI: THÔNG TIN
        ══════════════════════════════════ -->
        <div style="flex:1; min-width:280px; max-width:400px; display:flex; flex-direction:column; gap:16px;">

            <!-- Thông tin tài khoản ngân hàng -->
            <div class="room-card">
                <div class="card-body">
                    <div class="info-title" style="margin-bottom:12px;">🏦 Thông Tin Chuyển Khoản</div>
                    <?php
                    $rows = [
                        ['Ngân hàng',     defined('BANK_ID')      ? BANK_ID      : 'MB Bank'],
                        ['Số tài khoản',  defined('BANK_ACCOUNT') ? BANK_ACCOUNT : '—'],
                        ['Chủ tài khoản', defined('ACCOUNT_NAME') ? ACCOUNT_NAME : '—'],
                        ['Số tiền',       $amountFmt . 'đ'],
                        ['Nội dung CK',   $content],
                    ];
                    foreach ($rows as [$label, $val]):
                    ?>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;
                                padding:9px 0; border-bottom:1px solid #f0f0f0;">
                        <span style="font-size:13px; color:#888; flex-shrink:0; min-width:120px;"><?= $label ?></span>
                        <span style="font-size:13px; font-weight:600; color:#222; text-align:right;
                                     word-break:break-all; cursor:pointer;"
                              onclick="copyText('<?= htmlspecialchars(addslashes($val)) ?>', this)"
                              title="Nhấn để sao chép">
                            <?= htmlspecialchars($val) ?> <span style="color:#2196f3; font-size:11px;">📋</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <p style="font-size:11px; color:#bbb; text-align:center; margin-top:10px;">
                        Nhấn vào giá trị để sao chép nhanh
                    </p>
                </div>
            </div>

            <!-- Thông tin booking -->
            <div class="room-card">
                <div class="card-body">
                    <div class="info-title" style="margin-bottom:10px;">📋 Thông Tin Đặt Phòng</div>
                    <p style="margin:4px 0; font-size:14px;">
                        Phòng <strong><?= htmlspecialchars($booking->getRoom()->getRoomNumber()) ?></strong>
                        — <?= htmlspecialchars($booking->getRoom()->getType()) ?>
                    </p>
                    <p style="margin:4px 0; font-size:13px; color:#555;">
                        📅 <?= $booking->getCheckIn()->format('d/m/Y') ?> →
                           <?= $booking->getCheckOut()->format('d/m/Y') ?>
                        (<?= $booking->getNights() ?> đêm)
                    </p>
                    <p style="margin:4px 0; font-size:13px; color:#555;">
                        👤 <?= htmlspecialchars($booking->getFullname()) ?>
                        &nbsp;|&nbsp; 📞 <?= htmlspecialchars($booking->getPhone()) ?>
                    </p>
                </div>
            </div>

            <!-- Hướng dẫn -->
            <div class="room-card">
                <div class="card-body">
                    <div class="info-title" style="margin-bottom:10px;">📱 Hướng Dẫn</div>
                    <ol style="padding-left:18px; margin:0; font-size:13px; color:#555; line-height:2.1;">
                        <li>Mở app ngân hàng → chọn <strong>Quét QR</strong></li>
                        <li>Quét mã bên trái</li>
                        <li>Kiểm tra số tiền & nội dung CK</li>
                        <li>Xác nhận thanh toán</li>
                        <li>Trang này tự động cập nhật ✅</li>
                    </ol>
                    <div style="margin-top:12px; background:#e3f2fd; border-radius:8px;
                                padding:10px 14px; font-size:12px; color:#1565c0;">
                        ℹ️ Hệ thống nhận kết quả tự động trong vòng <strong>5–30 giây</strong>
                        sau khi bạn chuyển tiền thành công.
                    </div>
                </div>
            </div>

            <!-- Nút -->
            <div style="display:flex; gap:12px;">
                <a href="?action=rooms" class="btn-detail" style="flex:1; text-align:center;">🛏️ Đặt phòng khác</a>
                <a href="?action=home"    class="btn-book"   style="flex:1; text-align:center;">🏠 Trang chủ</a>
            </div>

        </div>
    </div><!-- end flex -->

</div>
</section>
</main>

<style>
@keyframes blink {
    0%,100% { opacity:1; }
    50%      { opacity:.2; }
}
</style>

<script>
const BOOKING_ID  = <?= (int)$booking->getId() ?>;
const BASE_URL    = '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/?';

// ── 1. ĐẾM NGƯỢC 15 PHÚT ──
(function(){
    let secs = 15 * 60;
    const el = document.getElementById('timer');
    const id = setInterval(() => {
        secs--;
        if (secs <= 0) {
            clearInterval(id);
            el.closest('#badge-pending').innerHTML =
                '⚠️ QR hết hạn — <a href="javascript:location.reload()" style="color:#e65100;">Tải lại</a>';
            return;
        }
        const m = String(Math.floor(secs/60)).padStart(2,'0');
        const s = String(secs%60).padStart(2,'0');
        el.textContent = m+':'+s;
    }, 1000);
})();

// ── 2. POLLING MỖI 5 GIÂY ──
(function(){
    let tries = 0;
    const MAX = 180; // 15 phút

    const poll = setInterval(() => {
        if (++tries > MAX) { clearInterval(poll); return; }

        fetch(BASE_URL + 'action=payment/check&booking_id=' + BOOKING_ID)
            .then(r => r.json())
            .then(data => {
                if (data.confirmed) {
                    clearInterval(poll);
                    onPaymentSuccess(data.redirectUrl);
                }
            })
            .catch(() => {}); // Bỏ qua lỗi mạng tạm thời
    }, 5000);
})();

// ── 3. KHI THANH TOÁN THÀNH CÔNG ──
function onPaymentSuccess(redirectUrl) {
    // Ẩn badge "đang chờ", hiện overlay xanh lên QR
    document.getElementById('badge-pending').style.display = 'none';
    const overlay = document.getElementById('qr-success-overlay');
    overlay.style.display = 'flex';

    // Chuyển hướng sau 1.5s
    setTimeout(() => {
        window.location.href = redirectUrl || (BASE_URL + 'action=payment/success&booking_id=' + BOOKING_ID);
    }, 1500);
}

// ── 4. SAO CHÉP ──
function copyText(text, el) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = el.innerHTML;
        el.innerHTML = '<span style="color:#4caf50;font-weight:700;">✅ Đã sao chép!</span>';
        setTimeout(() => el.innerHTML = orig, 1500);
    });
}
function copyContent() {
    const text = document.getElementById('content-text').textContent.trim();
    navigator.clipboard.writeText(text).then(() => alert('Đã sao chép nội dung CK!'));
}
</script>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
