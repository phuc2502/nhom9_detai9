<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<?php
$bookingCode = str_pad($booking->getId(), 6, '0', STR_PAD_LEFT);
$amountFmt   = number_format($booking->getTotalPrice(), 0, ',', '.');
?>

<main>
<section class="section-rooms" style="padding:60px 0;">
<div class="container">

    <div style="max-width:580px; margin:0 auto;">

        <!-- ══ BANNER THÀNH CÔNG ══ -->
        <div style="text-align:center; margin-bottom:32px;">
            <div style="font-size:72px; margin-bottom:12px;">✅</div>
            <h2 style="font-size:28px; color:#1b5e20; margin:0 0 8px;">Thanh Toán Thành Công!</h2>
            <p style="color:#388e3c; font-size:15px; margin:0;">
                Cảm ơn <strong><?= htmlspecialchars($booking->getFullname()) ?></strong>!
                Đặt phòng của bạn đã được xác nhận.
            </p>
        </div>

        <!-- ══ THẺ XÁC NHẬN ══ -->
        <div class="room-card">
            <div class="card-body">

                <!-- Mã booking nổi bật -->
                <div style="text-align:center; background:linear-gradient(135deg,#e8f5e9,#f1f8e9);
                            border-radius:10px; padding:18px; margin-bottom:20px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Mã đặt phòng</div>
                    <div style="font-size:32px; font-weight:900; color:#2e7d32; letter-spacing:2px;">
                        #<?= $bookingCode ?>
                    </div>
                    <div style="margin-top:8px;">
                        <span style="background:#2e7d32; color:#fff; padding:4px 14px;
                                     border-radius:20px; font-size:12px; font-weight:600;">
                            ✅ Đã xác nhận
                        </span>
                    </div>
                </div>

                <!-- Chi tiết đặt phòng -->
                <?php
                $details = [
                    ['🛏️ Phòng',       $booking->getRoom()->getRoomNumber() . ' — ' . $booking->getRoom()->getType()],
                    ['📅 Nhận phòng',  $booking->getCheckIn()->format('d/m/Y')],
                    ['📅 Trả phòng',   $booking->getCheckOut()->format('d/m/Y')],
                    ['🌙 Số đêm',      $booking->getNights() . ' đêm'],
                    ['👥 Số khách',    $booking->getGuests() . ' khách'],
                    ['👤 Họ tên',      $booking->getFullname()],
                    ['📞 Điện thoại',  $booking->getPhone()],
                    ['📧 Email',       $booking->getEmail()],
                ];
                foreach ($details as [$label, $val]):
                ?>
                <div style="display:flex; justify-content:space-between; align-items:flex-start;
                            padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:14px;">
                    <span style="color:#888;"><?= $label ?></span>
                    <strong style="color:#222; text-align:right; max-width:60%;"><?= htmlspecialchars($val) ?></strong>
                </div>
                <?php endforeach; ?>

                <!-- Tổng tiền -->
                <div style="margin-top:16px; background:#fff8e1; border-radius:10px;
                            padding:16px; text-align:center;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Tổng tiền đã thanh toán</div>
                    <div style="font-size:32px; font-weight:800; color:#e65100;"><?= $amountFmt ?>đ</div>
                    <div style="font-size:11px; color:#aaa; margin-top:4px;">via VietQR · MB Bank</div>
                </div>

                <!-- Thời gian đặt -->
                <div style="text-align:right; font-size:11px; color:#bbb; margin-top:14px;">
                    Đặt lúc: <?= $booking->getCreatedAt() ?>
                </div>

            </div>
        </div>

        <!-- ══ NÚT HÀNH ĐỘNG ══ -->
        <div style="display:flex; gap:12px; margin-top:20px; justify-content:center;">
            <a href="?action=booking" class="btn-detail" style="flex:1; text-align:center; max-width:200px;">
                ← Đặt phòng khác
            </a>
            <a href="?action=home" class="btn-book" style="flex:1; text-align:center; max-width:200px;">
                🏠 Về trang chủ
            </a>
        </div>

    </div>
</div>
</section>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
