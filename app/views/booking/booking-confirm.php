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
                ['Thanh toán',  'Tại quầy'],
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
