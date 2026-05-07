<?php
// File: app/views/booking/booking.php
// FIX 1: Thay toàn bộ thông báo lỗi trình duyệt mặc định → popup tùy chỉnh
// FIX 2: (được xử lý tại booking-confirm.php) nút "Đặt phòng khác" → ?action=rooms
include ROOT_PATH . '/app/views/layout/header.php';
?>

<main>
<section class="section-rooms">
<div class="container">
<div class="section-header"><h2>Đặt Phòng</h2></div>

<!-- ========================================================
     POPUP THÔNG BÁO LỖI TÙY CHỈNH
     Thay thế hoàn toàn alert() và HTML5 validation mặc định.
     Hiển thị ở giữa màn hình, có animation, bấm ngoài để đóng.
     ======================================================== -->
<div id="toast-overlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45);
            z-index:9999; align-items:center; justify-content:center;">
    <div id="toast-box"
         style="background:#fff; border-radius:14px; padding:28px 32px; max-width:380px;
                width:90%; text-align:center; box-shadow:0 8px 40px rgba(0,0,0,.22);
                animation:toastIn .22s ease;">
        <div id="toast-icon" style="font-size:2.6rem; margin-bottom:10px;"></div>
        <div id="toast-title"
             style="font-weight:700; font-size:1.1rem; color:#1a1a1a; margin-bottom:6px;"></div>
        <div id="toast-msg"
             style="font-size:.93rem; color:#555; line-height:1.55;"></div>
        <button onclick="closeToast()"
                style="margin-top:20px; background:#009688; color:#fff; border:none;
                       padding:10px 32px; border-radius:8px; font-size:.95rem;
                       font-weight:600; cursor:pointer;">OK</button>
    </div>
</div>
<style>
@keyframes toastIn {
    from { transform:scale(.88); opacity:0; }
    to   { transform:scale(1);   opacity:1; }
}
</style>
<script>
/* ---- Popup helper ----
   icon: '⚠️' | '❌' | '✅' | '💳' v.v.
   title: tiêu đề
   msg: nội dung
*/
function showToast(icon, title, msg) {
    document.getElementById('toast-icon').textContent  = icon;
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-msg').innerHTML     = msg;
    var ov = document.getElementById('toast-overlay');
    ov.style.display = 'flex';
}
function closeToast() {
    document.getElementById('toast-overlay').style.display = 'none';
}
// Bấm ngoài box cũng đóng popup
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('toast-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeToast();
    });
});
</script>

<!-- Thông báo lỗi từ server (PHP) — hiển thị qua popup, không dùng div cứng -->
<?php if (isset($error) && $error !== ''): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('❌', 'Đặt phòng thất bại', <?= json_encode(htmlspecialchars($error)) ?>);
});
</script>
<?php endif; ?>

<div class="room-card" style="max-width:620px; margin:0 auto;">
<div class="card-body">

<!--
    novalidate: TẮT hoàn toàn HTML5 validation mặc định của trình duyệt.
    Lý do: HTML5 validation hiển thị tooltip kiểu "Value must be ≥ 1" (hình 1)
           và dropdown mặc định (hình 3) — xấu, không nhất quán với UI.
    Thay bằng: validateForm() trong JS trước khi submit.
-->
<form id="booking-form"
      action="?action=booking&do=store"
      method="post"
      novalidate>

    <!-- Họ tên -->
    <div class="card-info">
        <div class="info-title">Họ và tên <span style="color:#e53935;">*</span></div>
        <input type="text" name="fullname" id="f-fullname"
               placeholder="Nhập họ và tên"
               value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Email -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Email <span style="color:#e53935;">*</span></div>
        <input type="email" name="email" id="f-email"
               placeholder="Nhập email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Số điện thoại + mã quốc gia -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Số điện thoại di động <span style="color:#e53935;">*</span></div>
        <div id="phone-wrap" style="display:flex; align-items:stretch; border:1px solid #ccc;
             border-radius:6px; overflow:visible; background:#fff; position:relative;">
            <!-- Nút mã quốc gia -->
            <div id="phone-flag-btn"
                 onclick="togglePhoneDD()"
                 style="display:flex; align-items:center; gap:5px; padding:0 10px;
                        background:#f5f5f5; border-right:1px solid #ddd; cursor:pointer;
                        user-select:none; border-radius:5px 0 0 5px; min-width:82px; white-space:nowrap;">
                <span id="sel-flag" style="font-size:17px;">🇻🇳</span>
                <span id="sel-code" style="font-size:14px; font-weight:700; color:#333;">+84</span>
                <span style="font-size:10px; color:#888;">▾</span>
            </div>
            <!-- Dropdown -->
            <div id="phone-dd"
                 style="display:none; position:absolute; top:calc(100% + 3px); left:0;
                        width:270px; background:#fff; border:1px solid #ddd; border-radius:10px;
                        box-shadow:0 8px 24px rgba(0,0,0,.13); z-index:9999; overflow:hidden;">
                <div style="padding:8px 9px; border-bottom:1px solid #f0f0f0;">
                    <input type="text" id="phone-search" placeholder="Tìm quốc gia..."
                           oninput="filterPhoneDD(this.value)" onclick="event.stopPropagation()"
                           style="width:100%; padding:6px 9px; border:1px solid #e0e0e0;
                                  border-radius:6px; font-size:13px; outline:none; font-family:inherit;">
                </div>
                <div id="phone-list" style="max-height:200px; overflow-y:auto;"></div>
            </div>
            <!-- Input số -->
            <input type="tel" name="phone" id="f-phone"
                   placeholder="Số điện thoại"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                   inputmode="numeric"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                   style="flex:1; border:none; outline:none; padding:10px 11px;
                          font-size:14px; font-family:inherit; color:#333;
                          background:transparent; min-width:0;">
            <input type="hidden" name="phone_prefix" id="phone-prefix" value="+84">
        </div>
        <small style="color:#aaa; font-size:11px; margin-top:4px; display:block;">
            VD: 0912 345 678 (không cần nhập +84)
        </small>
    </div>

    <!-- Ngày nhận phòng -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Ngày nhận phòng <span style="color:#e53935;">*</span></div>
        <input type="date" name="checkin" id="checkin"
               min="<?= date('Y-m-d') ?>"
               value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Ngày trả phòng -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Ngày trả phòng <span style="color:#e53935;">*</span></div>
        <input type="date" name="checkout" id="checkout"
               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
               value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Số người lớn -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Số người lớn <span style="color:#e53935;">*</span></div>
        <input type="number" name="adults" id="f-adults"
               min="1"
               value="<?= max(1, (int)($_POST['adults'] ?? 1)) ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Số trẻ em -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Số trẻ em</div>
        <input type="number" name="children" id="f-children"
               min="0"
               value="<?= max(0, (int)($_POST['children'] ?? 0)) ?>"
               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
    </div>

    <!-- Ghi chú -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Ghi chú đặc biệt</div>
        <textarea name="note" rows="3" placeholder="Ví dụ: yêu cầu giường phụ..."
                  style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"
        ><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
    </div>

    <!-- Phương thức thanh toán -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Phương thức thanh toán <span style="color:#e53935;">*</span></div>
        <!--
            input[type=hidden] lưu giá trị được chọn.
            Không dùng radio/select để tránh style mặc định của trình duyệt.
        -->
        <input type="hidden" name="payment" id="payment-val"
               value="<?= htmlspecialchars($_POST['payment'] ?? '') ?>">

        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:8px;">

            <label id="opt-sepay" onclick="selectPay('sepay')"
                   style="flex:1; min-width:190px; cursor:pointer;
                          border:2px solid <?= ($_POST['payment'] ?? '') === 'sepay' ? '#009688' : '#e0e0e0' ?>;
                          background:<?= ($_POST['payment'] ?? '') === 'sepay' ? '#f0fdf9' : '#fff' ?>;
                          border-radius:10px; padding:14px 16px;
                          display:flex; align-items:center; gap:12px; transition:all .2s;">
                <div style="font-size:26px;">📱</div>
                <div>
                    <div style="font-weight:700; font-size:14px;">Thanh toán VietQR</div>
                    <div style="font-size:12px; color:#009688; margin-top:2px;">Quét QR · Xác nhận tự động</div>
                </div>
                <div id="chk-sepay"
                     style="margin-left:auto; width:20px; height:20px; border-radius:50%;
                            border:2px solid #009688;
                            background:<?= ($_POST['payment'] ?? '') === 'sepay' ? '#009688' : '#fff' ?>;
                            display:flex; align-items:center; justify-content:center;
                            color:#fff; font-size:11px;">
                    <?= ($_POST['payment'] ?? '') === 'sepay' ? '✓' : '' ?>
                </div>
            </label>

            <label id="opt-counter" onclick="selectPay('counter')"
                   style="flex:1; min-width:190px; cursor:pointer;
                          border:2px solid <?= ($_POST['payment'] ?? '') === 'counter' ? '#009688' : '#e0e0e0' ?>;
                          background:<?= ($_POST['payment'] ?? '') === 'counter' ? '#f0fdf9' : '#fff' ?>;
                          border-radius:10px; padding:14px 16px;
                          display:flex; align-items:center; gap:12px; transition:all .2s;">
                <div style="font-size:26px;">🏨</div>
                <div>
                    <div style="font-weight:700; font-size:14px;">Thanh toán tại quầy</div>
                    <div style="font-size:12px; color:#555; margin-top:2px;">Tiền mặt · Thẻ · Check-in</div>
                </div>
                <div id="chk-counter"
                     style="margin-left:auto; width:20px; height:20px; border-radius:50%;
                            border:2px solid #009688;
                            background:<?= ($_POST['payment'] ?? '') === 'counter' ? '#009688' : '#fff' ?>;
                            display:flex; align-items:center; justify-content:center;
                            color:#fff; font-size:11px;">
                    <?= ($_POST['payment'] ?? '') === 'counter' ? '✓' : '' ?>
                </div>
            </label>

        </div>

        <div id="sepay-hint"
             style="display:<?= ($_POST['payment'] ?? '') === 'sepay' ? 'block' : 'none' ?>;
                    margin-top:10px; background:#e3f2fd; border-radius:8px;
                    padding:10px 14px; font-size:12px; color:#1565c0;">
            💡 Sau khi xác nhận, bạn sẽ thấy mã QR để quét thanh toán.
               Hệ thống tự động xác nhận khi nhận được tiền.
        </div>
    </div>

    <!-- Chọn phòng -->
    <div class="card-info" style="margin-top:15px;">
        <div class="info-title">Phòng <span style="color:#e53935;">*</span></div>
        <?php if (!empty($preselectedRoom)): ?>
            <input type="hidden" name="room_id" value="<?= $preselectedRoom->getId() ?>">
            <input type="hidden" id="room-max-guests" value="<?= $preselectedRoom->getMaxGuests() ?>">
            <input type="hidden" id="room-max-adults" value="<?= $preselectedRoom->getMaxAdults() ?>">
            <input type="hidden" id="room-max-children" value="<?= $preselectedRoom->getMaxChildren() ?>">
            <div style="padding:14px 18px;
                        background:linear-gradient(135deg,#f0fdf9 0%,#e6f7f5 100%);
                        border:1.5px solid #009688; border-radius:10px;
                        display:flex; align-items:center; gap:14px;">
                <div style="width:44px; height:44px; background:#009688; border-radius:8px;
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="font-size:1.4rem;">🛏️</span>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700; font-size:1rem;">
                        Phòng <?= htmlspecialchars($preselectedRoom->getType()) ?>
                        <span style="font-weight:400; color:#888; font-size:.88rem; margin-left:6px;">
                            · Số phòng <?= htmlspecialchars($preselectedRoom->getRoomNumber()) ?>
                        </span>
                    </div>
                    <div style="margin-top:4px; display:flex; gap:12px; flex-wrap:wrap;">
                        <span style="color:#009688; font-weight:700;">
                            <?= number_format($preselectedRoom->getPricePerNight(), 0, ',', '.') ?>đ
                            <span style="font-weight:400; font-size:.85rem; color:#555;">/đêm</span>
                        </span>
                        <span style="background:#e0f7f4; color:#00796b; font-size:.8rem;
                                     padding:2px 8px; border-radius:20px; font-weight:500;">
                            👤 <?= $preselectedRoom->getMaxAdults() ?> người lớn &nbsp;
                            👶 <?= $preselectedRoom->getMaxChildren() ?> trẻ em
                        </span>
                    </div>
                </div>
                <a href="?action=rooms"
                   style="font-size:.83rem; color:#009688; font-weight:500;
                          text-decoration:none; border:1px solid #009688; border-radius:6px;
                          padding:5px 10px; white-space:nowrap;"
                   onmouseover="this.style.background='#009688';this.style.color='#fff'"
                   onmouseout="this.style.background='transparent';this.style.color='#009688'">
                    ⇄ Đổi phòng
                </a>
            </div>
        <?php else: ?>
            <!--
                FIX lỗi 1 (hình 3): select này KHÔNG có required attribute.
                Validation được xử lý hoàn toàn trong validateForm() bên dưới.
                → Không bao giờ hiện dropdown tooltip mặc định của trình duyệt.
            -->
            <select name="room_id" id="f-room"
                    onchange="updateRoomMax(this)"
                    style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                <option value="" data-max="0" data-maxadults="0" data-maxchildren="0">-- Chọn loại phòng --</option>
                <?php foreach ($rooms ?? [] as $room):
                    $sel = (isset($_POST['room_id']) && $_POST['room_id'] == $room->getId()) ? 'selected' : '';
                ?>
                    <option value="<?= $room->getId() ?>" <?= $sel ?>
                            data-max="<?= $room->getMaxGuests() ?>"
                            data-maxadults="<?= $room->getMaxAdults() ?>"
                            data-maxchildren="<?= $room->getMaxChildren() ?>">
                        Phòng <?= htmlspecialchars($room->getRoomNumber()) ?>
                        - <?= htmlspecialchars($room->getType()) ?>
                        (<?= number_format($room->getPricePerNight(), 0, ',', '.') ?>đ/đêm
                        - tối đa <?= $room->getMaxAdults() ?> người lớn, <?= $room->getMaxChildren() ?> trẻ em)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="room-max-guests" value="0">
            <input type="hidden" id="room-max-adults" value="0">
            <input type="hidden" id="room-max-children" value="0">
        <?php endif; ?>
    </div>

    <!-- Lưu ý thông tin liên hệ -->
    <div style="margin-top:20px; background:#f5f5f5; border-radius:8px;
                padding:12px 16px; font-size:13px; color:#555; line-height:1.6;">
        Vui lòng đảm bảo thông tin liên hệ của quý khách là chính xác. Chúng tôi sẽ sử dụng
        thông tin đó để gửi xác nhận đặt chỗ và mọi thông báo nhắc nhở để hỗ trợ quý khách
        hoàn tất đặt chỗ.
    </div>

    <!-- Nút submit -->
    <div class="card-actions" style="margin-top:20px; justify-content:center;">
        <button type="button" onclick="validateForm()" class="btn-book">
            Xác nhận đặt phòng
        </button>
    </div>

</form>
</div><!-- /.card-body -->
</div><!-- /.room-card -->
</div></section></main>

<!-- ================================================================
     JS: Validate form → hiển thị popup lỗi, KHÔNG dùng alert() hay
         HTML5 default validation tooltip.

     Luồng:
     1. User bấm "Xác nhận đặt phòng"
     2. validateForm() chạy, kiểm tra từng trường
     3. Nếu lỗi → showToast() hiện popup → dừng, KHÔNG submit
     4. Nếu tất cả hợp lệ → form.submit() gửi lên server
     ================================================================ -->
<script>
/* ---- selectPay: xử lý chọn phương thức thanh toán ---- */
function selectPay(val) {
    document.getElementById('payment-val').value = val;
    ['sepay', 'counter'].forEach(function(v) {
        var active = (v === val);
        document.getElementById('opt-' + v).style.borderColor = active ? '#009688' : '#e0e0e0';
        document.getElementById('opt-' + v).style.background  = active ? '#f0fdf9' : '#fff';
        document.getElementById('chk-' + v).style.background  = active ? '#009688' : '#fff';
        document.getElementById('chk-' + v).textContent       = active ? '✓' : '';
    });
    document.getElementById('sepay-hint').style.display = (val === 'sepay') ? 'block' : 'none';
}

/* ---- validateForm: kiểm tra toàn bộ, hiện popup nếu sai ---- */
function validateForm() {
    /* Lấy giá trị các trường */
    var fullname  = document.getElementById('f-fullname').value.trim();
    var email     = document.getElementById('f-email').value.trim();
    var phone     = document.getElementById('f-phone').value.trim();
    var checkin   = document.getElementById('checkin').value;
    var checkout  = document.getElementById('checkout').value;
    var adults    = parseInt(document.getElementById('f-adults').value) || 0;
    var children  = parseInt(document.getElementById('f-children').value) || 0;
    var payment   = document.getElementById('payment-val').value;

    /* Lấy room_id: có thể là select dropdown hoặc hidden input (preselected) */
    var roomEl  = document.getElementById('f-room');
    var room_id = roomEl ? roomEl.value : '<?= isset($preselectedRoom) ? $preselectedRoom->getId() : '' ?>';

    var today    = new Date(); today.setHours(0,0,0,0);

    /* ---- Kiểm tra từng trường, hiện popup ngay khi gặp lỗi đầu tiên ---- */

    if (!fullname) {
        showToast('✍️', 'Thiếu thông tin', 'Vui lòng nhập <strong>Họ và tên</strong>.');
        return;
    }

    /* Regex email đơn giản */
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showToast('📧', 'Email không hợp lệ', 'Vui lòng nhập đúng định dạng email.<br><small>Ví dụ: ten@gmail.com</small>');
        return;
    }

    /* SĐT: không được rỗng, chỉ chứa chữ số, độ dài 6–15 */
    if (!phone) {
        showToast('📞', 'Thiếu số điện thoại', 'Vui lòng nhập <strong>số điện thoại</strong>.');
        return;
    }
    if (!/^[0-9]+$/.test(phone)) {
        showToast('📞', 'Số điện thoại không hợp lệ', 'Số điện thoại chỉ được chứa <strong>chữ số 0–9</strong>, không có chữ hay ký tự đặc biệt.');
        return;
    }
    if (phone.length < 6 || phone.length > 15) {
        showToast('📞', 'Số điện thoại không hợp lệ', 'Số điện thoại phải có <strong>6–15 chữ số</strong>.');
        return;
    }

    if (!checkin) {
        showToast('📅', 'Thiếu ngày nhận phòng', 'Vui lòng chọn <strong>Ngày nhận phòng</strong>.');
        return;
    }

    if (!checkout) {
        showToast('📅', 'Thiếu ngày trả phòng', 'Vui lòng chọn <strong>Ngày trả phòng</strong>.');
        return;
    }

    /* check-in không được là ngày hôm qua trở về trước */
    var ciDate = new Date(checkin);
    if (ciDate < today) {
        showToast('⚠️', 'Ngày không hợp lệ', 'Ngày nhận phòng không được là ngày đã qua.');
        return;
    }

    /* check-out phải sau check-in ít nhất 1 ngày */
    var coDate = new Date(checkout);
    if (coDate <= ciDate) {
        showToast('⚠️', 'Ngày không hợp lệ',
            'Ngày trả phòng phải <strong>sau</strong> ngày nhận phòng ít nhất 1 ngày.');
        return;
    }

    /* Số người lớn tối thiểu 1 */
    if (!adults || adults < 1) {
        showToast('👤', 'Số người lớn không hợp lệ',
            'Phải có ít nhất <strong>1 người lớn</strong> trong mỗi booking.');
        return;
    }

    /* Kiểm tra số người lớn không vượt max_adults */
    var maxAdults = parseInt(document.getElementById('room-max-adults')?.value) || 0;
    if (maxAdults > 0 && adults > maxAdults) {
        showToast('👤', 'Số người lớn vượt giới hạn',
            'Phòng này chỉ chứa tối đa <strong>' + maxAdults + ' người lớn</strong>.<br>' +
            'Bạn đang nhập <strong>' + adults + ' người lớn</strong>.');
        return;
    }

    /* Số trẻ em không được âm */
    if (children < 0) {
        showToast('👶', 'Số trẻ em không hợp lệ', 'Số trẻ em không được nhỏ hơn 0.');
        return;
    }

    /* Kiểm tra số trẻ em không vượt max_children */
    var maxChildren = parseInt(document.getElementById('room-max-children')?.value) || 0;
    if (children > maxChildren) {
        showToast('👶', 'Số trẻ em vượt giới hạn',
            'Phòng này chỉ chứa tối đa <strong>' + maxChildren + ' trẻ em</strong>.<br>' +
            'Bạn đang nhập <strong>' + children + ' trẻ em</strong>.');
        return;
    }

    /* Tổng khách không được vượt sức chứa phòng */
    var maxGuests = parseInt(document.getElementById('room-max-guests')?.value) || 0;
    if (maxGuests > 0 && (adults + children) > maxGuests) {
        showToast('👥', 'Vượt quá sức chứa phòng',
            'Tổng số khách (<strong>' + (adults + children) + ' người</strong>) vượt quá ' +
            'sức chứa tối đa của phòng (<strong>' + maxGuests + ' khách</strong>).<br>' +
            'Vui lòng giảm số người lớn hoặc trẻ em.');
        return;
    }

    /* Phương thức thanh toán bắt buộc phải chọn */
    if (!payment) {
        showToast('💳', 'Chưa chọn phương thức thanh toán',
            'Vui lòng chọn <strong>Thanh toán VietQR</strong> hoặc <strong>Thanh toán tại quầy</strong>.');
        return;
    }

    /* Phòng phải được chọn (chỉ khi dropdown hiển thị) */
    if (roomEl && !room_id) {
        showToast('🛏️', 'Chưa chọn phòng', 'Vui lòng chọn <strong>phòng</strong> bạn muốn đặt.');
        return;
    }

    /* ---- Tất cả hợp lệ → submit ---- */
    document.getElementById('booking-form').submit();
}

/* ---- Đồng bộ min ngày trả phòng theo ngày nhận đã chọn ---- */
function updateCheckoutMin() {
    var ciVal = document.getElementById('checkin').value;
    var coEl  = document.getElementById('checkout');
    if (!ciVal) return;

    /* Tính ngày tối thiểu = check-in + 1 ngày */
    var minDate = new Date(ciVal);
    minDate.setDate(minDate.getDate() + 1);

    /* Định dạng lại thành YYYY-MM-DD để gán vào attribute min */
    var minStr = minDate.toISOString().split('T')[0];
    coEl.min = minStr; /* Trình duyệt tự disable các ngày trước min */

    /* Nếu checkout hiện tại <= checkin (cùng ngày hoặc trước) → xóa để user chọn lại */
    if (coEl.value && coEl.value < minStr) coEl.value = '';
}

document.getElementById('checkin').addEventListener('change', updateCheckoutMin);
/* Gọi ngay khi load để áp dụng nếu đã có giá trị sẵn (VD: giữ lại sau submit lỗi) */
updateCheckoutMin();

/* ---- Phone dropdown: mã quốc gia ---- */
var PHONE_COUNTRIES = [
    {name:'Việt Nam',    code:'+84',  flag:'🇻🇳'},
    {name:'Hoa Kỳ',      code:'+1',   flag:'🇺🇸'},
    {name:'Nhật Bản',    code:'+81',  flag:'🇯🇵'},
    {name:'Hàn Quốc',    code:'+82',  flag:'🇰🇷'},
    {name:'Trung Quốc',  code:'+86',  flag:'🇨🇳'},
    {name:'Anh',         code:'+44',  flag:'🇬🇧'},
    {name:'Pháp',        code:'+33',  flag:'🇫🇷'},
    {name:'Đức',         code:'+49',  flag:'🇩🇪'},
    {name:'Úc',          code:'+61',  flag:'🇦🇺'},
    {name:'Singapore',   code:'+65',  flag:'🇸🇬'},
    {name:'Thái Lan',    code:'+66',  flag:'🇹🇭'},
    {name:'Malaysia',    code:'+60',  flag:'🇲🇾'},
    {name:'Indonesia',   code:'+62',  flag:'🇮🇩'},
    {name:'Philippines', code:'+63',  flag:'🇵🇭'},
    {name:'Campuchia',   code:'+855', flag:'🇰🇭'},
    {name:'Canada',      code:'+1',   flag:'🇨🇦'},
    {name:'Ý',           code:'+39',  flag:'🇮🇹'},
    {name:'Tây Ban Nha', code:'+34',  flag:'🇪🇸'},
    {name:'Nga',         code:'+7',   flag:'🇷🇺'},
    {name:'UAE',         code:'+971', flag:'🇦🇪'},
];
function renderPhoneList(filter) {
    filter = (filter||'').toLowerCase();
    var html = PHONE_COUNTRIES
        .filter(function(c){ return c.name.toLowerCase().indexOf(filter)>-1 || c.code.indexOf(filter)>-1; })
        .map(function(c,_,arr){
            var idx = PHONE_COUNTRIES.indexOf(c);
            return '<div onclick="pickCountry('+idx+')" '+
                   'style="display:flex;align-items:center;gap:9px;padding:8px 13px;cursor:pointer;font-size:13px;" '+
                   'onmouseover="this.style.background=\'#f5f5f5\'" onmouseout="this.style.background=\'\'">'+
                   '<span style="font-size:17px;">'+c.flag+'</span>'+
                   '<span style="flex:1;color:#333;">'+c.name+'</span>'+
                   '<span style="color:#009688;font-weight:700;">'+c.code+'</span></div>';
        }).join('');
    document.getElementById('phone-list').innerHTML = html;
}
function filterPhoneDD(val){ renderPhoneList(val); }
function togglePhoneDD(){
    var dd = document.getElementById('phone-dd');
    var open = dd.style.display === 'block';
    dd.style.display = open ? 'none' : 'block';
    if (!open){ renderPhoneList(''); document.getElementById('phone-search').focus(); }
}
function pickCountry(idx){
    var c = PHONE_COUNTRIES[idx];
    document.getElementById('sel-flag').textContent  = c.flag;
    document.getElementById('sel-code').textContent  = c.code;
    document.getElementById('phone-prefix').value    = c.code;
    document.getElementById('phone-dd').style.display = 'none';
}
document.addEventListener('click', function(e){
    var btn = document.getElementById('phone-flag-btn');
    var dd  = document.getElementById('phone-dd');
    if (btn && !btn.contains(e.target) && !dd.contains(e.target))
        dd.style.display = 'none';
});
/* Viền xanh khi focus vào phone-wrap */
document.getElementById('f-phone').addEventListener('focus', function(){
    document.getElementById('phone-wrap').style.borderColor = '#009688';
    document.getElementById('phone-wrap').style.boxShadow  = '0 0 0 2px rgba(0,150,136,.15)';
});
document.getElementById('f-phone').addEventListener('blur', function(){
    document.getElementById('phone-wrap').style.borderColor = '#ccc';
    document.getElementById('phone-wrap').style.boxShadow  = '';
});

/* Cập nhật max-guests khi đổi phòng qua dropdown */
function updateRoomMax(sel) {
    var opt = sel.options[sel.selectedIndex];
    var max = parseInt(opt.getAttribute('data-max')) || 0;
    var maxAdults   = parseInt(opt.getAttribute('data-maxadults'))   || 0;
    var maxChildren = parseInt(opt.getAttribute('data-maxchildren')) || 0;
    document.getElementById('room-max-guests').value   = max;
    document.getElementById('room-max-adults').value   = maxAdults;
    document.getElementById('room-max-children').value = maxChildren;
}
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('f-room');
    if (sel) updateRoomMax(sel);
});
</script>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
