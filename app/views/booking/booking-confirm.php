<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<?php
$bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
$amountFmt   = number_format($booking->getTotalPrice(), 0, ',', '.');
$depositAmt  = number_format($booking->getTotalPrice() * 0.3, 0, ',', '.');
?>

<main>
<section class="section-rooms" style="padding:60px 0;">
<div class="container">
<div style="max-width:600px; margin:0 auto;">

    <!-- Banner xác nhận -->
    <div style="text-align:center; margin-bottom:32px;">
        <div style="font-size:72px; margin-bottom:12px;">🎉</div>
        <h2 style="font-size:26px; color:#1a1a2e; margin:0 0 8px;">Đặt Phòng Thành Công!</h2>
        <p style="color:#555; font-size:15px;">
            Cảm ơn <strong><?= htmlspecialchars($booking->getFullname()) ?></strong>!
            Thông tin xác nhận đã gửi về email của bạn.
        </p>
    </div>

    <!-- Mã booking -->
    <div class="room-card" style="margin-bottom:20px;">
        <div class="card-body">
            <div style="text-align:center; background:linear-gradient(135deg,#e8f5e9,#f1f8e9);
                        border-radius:10px; padding:18px; margin-bottom:20px;">
                <div style="font-size:12px; color:#888; margin-bottom:4px;">Mã đặt phòng</div>
                <div style="font-size:32px; font-weight:900; color:#009688; letter-spacing:2px;">
                    #<?= $bookingCode ?>
                </div>
                <div style="margin-top:8px;">
                    <span class="badge green" style="padding:5px 16px; font-size:12px;">✅ Đã xác nhận</span>
                </div>
            </div>

            <?php
            $rows = [
                ['Phòng',       $booking->getRoom()->getType() . ' — Số ' . $booking->getRoom()->getRoomNumber()],
                ['Check-in',    $booking->getCheckIn()->format('d/m/Y')],
                ['Check-out',   $booking->getCheckOut()->format('d/m/Y')],
                ['Số đêm',      $booking->getNights() . ' đêm'],
                ['Số khách',    $booking->getGuests() . ' người'],
                ['Tổng tiền',   $amountFmt . 'đ'],
                ['Thanh toán',  'Tại quầy (đặt cọc 30%)'],
            ];
            foreach ($rows as [$label, $val]):
            ?>
            <div style="display:flex; justify-content:space-between; align-items:center;
                        padding:9px 0; border-bottom:1px solid #f0f0f0; font-size:14px;">
                <span style="color:#888;"><?= $label ?></span>
                <span style="font-weight:600; color:#222;"><?= htmlspecialchars($val) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Hướng dẫn đặt cọc -->
    <div class="room-card" style="margin-bottom:20px; border-left:4px solid #e65100;">
        <div class="card-body">
            <div class="info-title" style="color:#e65100; margin-bottom:12px;">
                💡 Yêu Cầu Đặt Cọc
            </div>
            <p style="font-size:14px; color:#555; margin-bottom:10px;">
                Để giữ chỗ, bạn cần chuyển khoản tiền cọc
                <strong style="color:#e65100; font-size:16px;"><?= $depositAmt ?>đ</strong>
                (30% tổng tiền) trong vòng <strong>24 giờ</strong>.
            </p>
            <div style="background:#fff3e0; border-radius:8px; padding:12px 14px; font-size:13px; color:#5d4037;">
                📧 Thông tin tài khoản nhận tiền cọc đã được gửi qua email của bạn.<br>
                ⚠️ Nếu không nhận được tiền cọc sau 24 giờ, đặt phòng sẽ tự động hủy.
            </div>
        </div>
    </div>

    <!-- Chính sách hủy -->
    <div class="room-card" style="margin-bottom:24px;">
        <div class="card-body">
            <div class="info-title" style="margin-bottom:12px;">📋 Chính Sách Hủy Phòng</div>
            <div style="font-size:13px; color:#555; line-height:2;">
                ✅ Hủy trước <strong>3 ngày</strong> check-in: hoàn <strong>100%</strong> tiền cọc<br>
                ⚠️ Hủy trước <strong>1–2 ngày</strong>: hoàn <strong>50%</strong> tiền cọc<br>
                ❌ Hủy ngày check-in hoặc không đến: <strong>mất toàn bộ</strong> tiền cọc<br>
                📞 Liên hệ lễ tân hoặc gọi hotline để yêu cầu hủy phòng.
            </div>
        </div>
    </div>

    <!-- Nút hành động -->
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="?action=home" class="btn-book" style="flex:1; text-align:center; text-decoration:none;">
            🏠 Về trang chủ
        </a>
        <a href="?action=rooms" class="btn-detail" style="flex:1; text-align:center;">
            🛏️ Đặt phòng khác
        </a>
    </div>

</div>
</div>
</section>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
