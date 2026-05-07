<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<?php
$bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
$amountFmt   = number_format($booking->getTotalPrice(), 0, ',', '.');
?>

<main>
<section class="section-rooms" style="padding:100px 0 40px;">
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

            <!-- Nút điều hướng -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="?action=rooms" class="btn-detail" style="flex:1; text-align:center; min-width:120px;">🛏️ Đặt phòng khác</a>
                <a href="?action=home"  class="btn-book"   style="flex:1; text-align:center; min-width:120px;">🏠 Trang chủ</a>
            </div>

            <!-- Nút hủy đặt phòng -->
            <div style="margin-top:8px;">
                <button onclick="openCancelModal()"
                        style="width:100%; padding:12px; background:#fff; border:2px solid #ef5350;
                               color:#ef5350; border-radius:8px; font-size:14px; font-weight:600;
                               cursor:pointer; transition:all .2s;"
                        onmouseover="this.style.background='#ef5350';this.style.color='#fff';"
                        onmouseout="this.style.background='#fff';this.style.color='#ef5350';">
                    ❌ Hủy đặt phòng
                </button>
            </div>

        </div>
    </div><!-- end flex -->

</div>
</section>
</main>

<!-- ══════════════ POPUP HỦY ══════════════ -->
<div id="modal-cancel" style="display:none; position:fixed; inset:0; z-index:9000;
     background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px 28px; max-width:380px;
                width:90%; text-align:center; box-shadow:0 12px 40px rgba(0,0,0,.25);">
        <div style="font-size:50px; margin-bottom:12px;">🗑️</div>
        <h3 style="margin:0 0 8px; font-size:20px; color:#1a1a1a;">Hủy đặt phòng?</h3>
        <p style="color:#666; font-size:14px; margin:0 0 24px; line-height:1.6;">
            Bạn có chắc muốn hủy không?<br>
            Phòng sẽ <strong>trở về trạng thái trống</strong> ngay lập tức.
        </p>
        <div style="display:flex; gap:12px;">
            <button onclick="closeCancelModal()"
                    style="flex:1; padding:12px; background:#f0f0f0; border:none; border-radius:10px;
                           font-size:15px; font-weight:600; cursor:pointer; color:#555;">
                Không, giữ lại
            </button>
            <button id="btn-do-cancel" onclick="doCancel()"
                    style="flex:1; padding:12px; background:#ef5350; border:none; border-radius:10px;
                           font-size:15px; font-weight:600; cursor:pointer; color:#fff;">
                Có, hủy ngay
            </button>
        </div>
    </div>
</div>

<!-- ══════════════ POPUP HẾT HẠN ══════════════ -->
<div id="modal-expired" style="display:none; position:fixed; inset:0; z-index:9000;
     background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px 28px; max-width:380px;
                width:90%; text-align:center; box-shadow:0 12px 40px rgba(0,0,0,.25);">
        <div style="font-size:50px; margin-bottom:12px;">⏰</div>
        <h3 style="margin:0 0 8px; font-size:20px; color:#1a1a1a;">Hết thời gian chờ</h3>
        <p style="color:#666; font-size:14px; margin:0 0 24px; line-height:1.6;">
            Thời gian giữ phòng 15 phút đã hết.<br>
            Phòng đã được <strong>trả về trạng thái trống</strong>.
        </p>
        <a href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/?action=rooms"
           style="display:block; padding:13px; background:#009688; border-radius:10px;
                  font-size:15px; font-weight:600; color:#fff; text-decoration:none;">
            🛏️ Chọn phòng khác
        </a>
    </div>
</div>

<style>
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
</style>

<script>
const BOOKING_ID = <?= (int)$booking->getId() ?>;
const BASE_URL   = '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/?';

// ══════════════════════════════════════════════
// 1. ĐẾM NGƯỢC 15 PHÚT — dùng sessionStorage
//    để đồng hồ không reset khi bấm back/reload
// ══════════════════════════════════════════════
(function () {
    const KEY = 'qr_expire_' + BOOKING_ID;
    let expireAt = parseInt(sessionStorage.getItem(KEY) || '0', 10);
    const now = Date.now();

    if (!expireAt || expireAt <= now) {
        expireAt = now + 15 * 60 * 1000;
        sessionStorage.setItem(KEY, expireAt);
    }

    const el = document.getElementById('timer');
    const id = setInterval(() => {
        const left = Math.max(0, Math.floor((expireAt - Date.now()) / 1000));
        if (left === 0) { clearInterval(id); return; }
        el.textContent =
            String(Math.floor(left / 60)).padStart(2, '0') + ':' +
            String(left % 60).padStart(2, '0');
    }, 500);
})();

// ══════════════════════════════════════════════
// 2. POLLING MỖI 5 GIÂY — kiểm tra thanh toán
//    và phát hiện hết hạn từ server
// ══════════════════════════════════════════════
(function () {
    let tries = 0;
    const MAX = 180;

    const poll = setInterval(() => {
        if (++tries > MAX) { clearInterval(poll); return; }

        fetch(BASE_URL + 'action=payment/check&booking_id=' + BOOKING_ID)
            .then(r => r.json())
            .then(data => {
                if (data.confirmed) {
                    clearInterval(poll);
                    sessionStorage.removeItem('qr_expire_' + BOOKING_ID);
                    // Hiện overlay thành công
                    document.getElementById('badge-pending').style.display = 'none';
                    document.getElementById('qr-success-overlay').style.display = 'flex';
                    setTimeout(() => {
                        window.location.href = data.redirectUrl ||
                            (BASE_URL + 'action=payment/success&booking_id=' + BOOKING_ID);
                    }, 1500);
                } else if (data.expired) {
                    clearInterval(poll);
                    sessionStorage.removeItem('qr_expire_' + BOOKING_ID);
                    showModal('modal-expired');
                }
            })
            .catch(() => {});
    }, 5000);
})();

// ══════════════════════════════════════════════
// 3. KHI NGƯỜI DÙNG RỜI TRANG (đóng tab / bấm link khác)
//    → cancel booking ngay, dùng sendBeacon để đáng tin cậy
// ══════════════════════════════════════════════
let _leaving = false;

window.addEventListener('pagehide', () => {
    if (_leaving) return;
    // Chỉ cancel nếu chưa thanh toán xong
    if (!document.getElementById('qr-success-overlay') ||
        document.getElementById('qr-success-overlay').style.display === 'none') {
        navigator.sendBeacon(
            BASE_URL + 'action=payment/leave&booking_id=' + BOOKING_ID
        );
    }
});

// ══════════════════════════════════════════════
// 4. POPUP HỦY
// ══════════════════════════════════════════════
function openCancelModal()  { showModal('modal-cancel'); }
function closeCancelModal() { hideModal('modal-cancel'); }

function doCancel() {
    const btn = document.getElementById('btn-do-cancel');
    btn.disabled = true;
    btn.textContent = '⏳ Đang hủy...';

    fetch(BASE_URL + 'action=payment/cancel&booking_id=' + BOOKING_ID, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                _leaving = true; // Ngăn pagehide gọi beacon thêm lần nữa
                sessionStorage.removeItem('qr_expire_' + BOOKING_ID);
                window.location.href = BASE_URL + 'action=rooms&notify=cancelled';
            } else {
                alert('❌ ' + (data.error || 'Không thể hủy'));
                btn.disabled = false;
                btn.textContent = 'Có, hủy ngay';
                closeCancelModal();
            }
        })
        .catch(() => {
            alert('❌ Lỗi kết nối, vui lòng thử lại.');
            btn.disabled = false;
            btn.textContent = 'Có, hủy ngay';
            closeCancelModal();
        });
}

// ══════════════════════════════════════════════
// 5. HELPERS
// ══════════════════════════════════════════════
function showModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function hideModal(id) {
    document.getElementById(id).style.display = 'none';
}
// Đóng modal khi click vùng nền mờ
['modal-cancel', 'modal-expired'].forEach(id => {
    document.getElementById(id).addEventListener('click', function (e) {
        if (e.target === this) hideModal(id);
    });
});

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
