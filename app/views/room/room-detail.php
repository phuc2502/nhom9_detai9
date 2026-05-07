<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<?php


$descMap = [
    'Standard'         => 'Phòng tiêu chuẩn với đầy đủ tiện nghi cơ bản, phù hợp cho khách công tác hoặc du lịch ngắn ngày.',
    'Standard Twin'    => 'Phòng tiêu chuẩn với 2 giường đơn, lý tưởng cho 2 người đi cùng nhau.',
    'Deluxe'           => 'Phòng Deluxe mang đến không gian rộng rãi, hiện đại với đầy đủ tiện nghi cao cấp và ban công riêng.',
    'Deluxe Sea View'  => 'Phòng Deluxe với tầm nhìn hướng biển tuyệt đẹp, không gian sang trọng và tiện nghi đẳng cấp.',
    'Suite'            => 'Phòng Suite sang trọng với phòng khách riêng biệt, bồn tắm cao cấp và dịch vụ hàng đầu.',
    'Suite Family'     => 'Phòng Suite gia đình rộng lớn với khu vực vui chơi cho trẻ em, phù hợp cho cả gia đình.',
    'Presidential Suite' => 'Phòng tổng thống đẳng cấp nhất với butler phục vụ 24/7, hồ tắm riêng và tầm nhìn toàn thành phố.',
    'Economy'          => 'Phòng Economy tiết kiệm với các tiện nghi cần thiết, lựa chọn tốt cho ngân sách hợp lý.',
    'Economy Twin'     => 'Phòng Economy với 2 giường đơn, tối ưu chi phí cho 2 người.',
    'Business'         => 'Phòng Business được thiết kế tối ưu cho khách công tác với bàn làm việc rộng và két an toàn.',
    'Business Deluxe'  => 'Phòng Business Deluxe nâng cấp với không gian làm việc chuyên nghiệp và phòng tắm đứng cao cấp.',
    'Luxury'           => 'Phòng Luxury với nội thất cao cấp, sàn gỗ sang trọng và ban công rộng ngắm thành phố.',
    'Luxury Sea View'  => 'Phòng Luxury hướng biển, kết hợp nội thất đẳng cấp cùng tầm nhìn đại dương thơ mộng.',
    'Penthouse'        => 'Penthouse độc quyền tại tầng cao nhất với hồ bơi riêng, sân thượng và trải nghiệm xa hoa tột đỉnh.',
    'Single Room'      => 'Phòng đơn ấm cúng, tiện lợi dành cho khách đi một mình.',
    'Double Room'      => 'Phòng đôi lãng mạn với giường đôi lớn và bàn trang điểm, phù hợp cho cặp đôi.',
    'Triple Room'      => 'Phòng ba giường đơn, tiện lợi cho nhóm bạn hoặc gia đình nhỏ.',
    'Quad Room'        => 'Phòng 4 giường rộng rãi, phù hợp nhất cho gia đình hoặc nhóm bạn.',
    'Family Room'      => 'Phòng gia đình ấm cúng với không gian sinh hoạt chung và giường cho cả bố mẹ lẫn con cái.',
];

$type      = $room->getType();
$amenities = $room->getAmenities();

if (empty($amenities)) {
    $amenities = ['Wi-Fi miễn phí', 'Máy lạnh', 'TV màn hình phẳng'];
}

$desc      = $descMap[$type] ?? 'Phòng tiện nghi với đầy đủ các dịch vụ hiện đại.';


?>

<main>
    <section class="section-rooms">
        <div class="container">

            <!-- Breadcrumb -->
            <div style="margin-bottom:16px; font-size:0.9rem; color:#888;">
                <a href="?action=rooms" style="color:#009688; text-decoration:none;">← Danh sách phòng</a>
                <span style="margin: 0 8px;">/</span>
                <span>Phòng <?= htmlspecialchars($type) ?></span>
            </div>

            <div class="room-detail-layout">

                <!-- Ảnh phòng -->
                <div class="room-detail-img">
                <img src="<?= getRoomImageUrl($type, 800, 500) ?>"
                 alt="Phòng <?= htmlspecialchars($type) ?>"
                 style="width:100%; border-radius:12px; object-fit:cover; max-height:420px;">
                </div>

                <!-- Thông tin phòng -->
                <div class="room-detail-info">

                    <h2 style="font-size:1.7rem; margin-bottom:6px;">
                        Phòng <?= htmlspecialchars($type) ?>
                    </h2>
                    <p style="color:#888; margin-bottom:16px;">Số phòng: <strong><?= htmlspecialchars($room->getRoomNumber()) ?></strong></p>

                    <!-- Giá + trạng thái -->
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                        <span style="font-size:1.5rem; font-weight:700; color:#009688;">
                            <?= number_format($room->getPricePerNight(), 0, ',', '.') ?>đ
                            <span style="font-size:1rem; font-weight:400; color:#555;">/đêm</span>
                        </span>
                        <?php if ($room->isActive()): ?>
                            <span class="badge green">Còn phòng</span>
                        <?php else: ?>
                            <span class="badge" style="background:#fee2e2;color:#dc2626;">Hết phòng</span>
                        <?php endif; ?>
                    </div>

                    <!-- Sức chứa -->
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px;
                                padding:10px 14px; background:#f0fdf9; border-radius:8px; border:1px solid #b2dfdb;">
                        <span style="font-size:1.2rem;">👥</span>
                        <span>Tối đa <strong><?= $room->getMaxGuests() ?> khách</strong></span>
                    </div>

                    <!-- Mô tả -->
                    <div class="card-info">
                        <div class="info-title">Mô tả</div>
                        <p style="color:#555; line-height:1.7;"><?= htmlspecialchars($desc) ?></p>
                    </div>

                    <!-- Tiện nghi -->
                    <div class="card-info">
                        <div class="info-title">Tiện nghi</div>
                        <div class="tag-list">
                            <?php foreach ($amenities as $item): ?>
                                <span class="tag">✓ <?= htmlspecialchars($item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Nút hành động -->
                    <div class="card-actions" style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
                        <?php if ($room->isActive()): ?>
                            <a href="?action=booking&room_id=<?= $room->getId() ?>" class="btn-book"
                               style="text-decoration:none; display:inline-block;">
                                🛎️ Đặt ngay
                            </a>
                        <?php else: ?>
                            <button class="btn-book" disabled
                                    style="opacity:0.5; cursor:not-allowed; background:#aaa;">
                                Hết phòng
                            </button>
                        <?php endif; ?>
                        <a href="?action=rooms" class="btn-detail">← Quay lại danh sách</a>
                    </div>

                </div>
            </div>

        </div>
    </section>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
