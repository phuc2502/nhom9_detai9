<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* ===== BANNER ===== */
    .tn-page-banner {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
        padding: 100px 0 50px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .tn-page-banner::before {
        content: '';
        position: absolute; inset: 0;
        background: url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=60') center/cover;
        opacity: 0.15;
    }
    .tn-page-banner .breadcrumb {
        position: relative;
        font-size: 13px;
        color: rgba(255,255,255,0.55);
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }
    .tn-page-banner .breadcrumb a { color: #c9a227; text-decoration: none; }
    .tn-page-banner .breadcrumb a:hover { text-decoration: underline; }
    .tn-page-banner h1 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 38px; color: #fff;
        position: relative; margin: 0 0 10px;
    }
    .tn-page-banner p {
        position: relative;
        color: rgba(255,255,255,0.65);
        font-size: 15px; margin: 0;
    }

    /* ===== SECTION CHÍNH ===== */
    .tn-amenities-section { background: #fff; padding: 70px 0 90px; }
    .tn-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

    .tn-section-heading { text-align: center; margin-bottom: 50px; }
    .tn-section-heading h2 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 32px; letter-spacing: 4px;
        color: #1a1a2e; margin: 0 0 12px;
        text-transform: uppercase;
    }
    .tn-section-heading .tn-underline {
        width: 60px; height: 3px;
        background: #2ecc71;
        margin: 0 auto 18px; border-radius: 2px;
    }
    .tn-section-heading p {
        font-size: 15px; color: #888;
        max-width: 580px; margin: 0 auto; line-height: 1.7;
    }

    /* ===== LƯỚI CARD ===== */
    .tn-amenities-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    @media (max-width: 860px) { .tn-amenities-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 540px) { .tn-amenities-grid { grid-template-columns: 1fr; } }

    /* ===== CARD ===== */
    .tn-amenity-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 28px 24px 26px;
        transition: box-shadow 0.25s, transform 0.25s;
        position: relative;
        overflow: hidden;
    }
    .tn-amenity-card:hover {
        box-shadow: 0 8px 28px rgba(0,0,0,0.10);
        transform: translateY(-4px);
    }
    .tn-amenity-card.featured {
        border-color: #2ecc71;
        box-shadow: 0 4px 20px rgba(46,204,113,0.15);
    }
    .tn-amenity-card.featured::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: #2ecc71;
        border-radius: 8px 8px 0 0;
    }
    .tn-amenity-card .card-header {
        display: flex; align-items: center;
        gap: 14px; margin-bottom: 14px;
    }
    .tn-amenity-card .icon-wrap {
        width: 46px; height: 46px;
        border-radius: 10px;
        background: #f0faf5;
        display: flex; align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px; color: #27ae60;
        transition: background 0.2s;
    }
    .tn-amenity-card:hover .icon-wrap { background: #e0f5ea; }
    .tn-amenity-card.featured .icon-wrap { background: #e0f5ea; color: #1e8449; }
    .tn-amenity-card .card-name {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 17px; color: #1a1a2e; font-weight: 600;
    }
    .tn-amenity-card .card-desc {
        font-size: 13.5px; color: #777;
        line-height: 1.75; margin: 0;
    }

    /* ===== THỐNG KÊ ===== */
    .tn-amenities-stats {
        display: flex; justify-content: center;
        gap: 60px; margin-top: 60px;
        padding-top: 40px;
        border-top: 1px solid #eee;
        flex-wrap: wrap;
    }
    .tn-stat-item { text-align: center; }
    .tn-stat-item .num {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 36px; color: #1a1a2e; display: block;
    }
    .tn-stat-item .tn-stat-label {
        font-size: 13px; color: #aaa;
        letter-spacing: 1px; text-transform: uppercase;
    }

    /* ===== NÚT CUỐI TRANG ===== */
    .tn-back-section {
        text-align: center; padding: 50px 0;
        background: #f5f5f5;
    }
    .tn-back-section p { font-size: 14px; color: #888; margin-bottom: 16px; }
    .tn-btn-home {
        display: inline-block; padding: 13px 36px;
        background: #1a1a2e; color: #fff;
        text-decoration: none; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        transition: background 0.2s; margin-right: 10px;
    }
    .tn-btn-home:hover { background: #c9a227; }
    .tn-btn-rooms {
        display: inline-block; padding: 13px 36px;
        background: #fff; color: #1a1a2e;
        text-decoration: none; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        border: 1px solid #ddd; transition: all 0.2s;
    }
    .tn-btn-rooms:hover { border-color: #1a1a2e; }
</style>

<main>

<!-- BANNER ĐẦU TRANG -->
<div class="tn-page-banner">
    <p class="breadcrumb">
        <a href="?action=home">Trang Chủ</a> &rsaquo; Tiện Nghi
    </p>
    <h1>Các Tiện Nghi</h1>
    <p>Tất cả những gì bạn cần cho một kỳ nghỉ hoàn hảo</p>
</div>

<!-- PHẦN CHÍNH: LƯỚI TIỆN NGHI -->
<section class="tn-amenities-section">
    <div class="tn-container">

        <div class="tn-section-heading">
            <h2>Tiện Nghi Nổi Bật</h2>
            <div class="tn-underline"></div>
            <p>Đến với chúng tôi, quý khách sẽ tận hưởng không gian đầy đủ tiện ích hiện đại.</p>
        </div>

        <?php
        $amenities = [
            [
                'name'     => 'Wifi',
                'icon'     => 'fa-solid fa-wifi',
                'featured' => false,
                'desc'     => 'Wifi tốc độ cao phủ sóng toàn bộ khách sạn, kết nối internet ổn định để làm việc, giải trí và liên lạc mọi lúc mọi nơi.',
            ],
            [
                'name'     => 'Điều Hoà',
                'icon'     => 'fa-solid fa-temperature-half',
                'featured' => true,
                'desc'     => 'Điều hoà thông minh tự động điều chỉnh nhiệt độ, mang lại không khí trong lành và dễ chịu suốt kỳ lưu trú.',
            ],
            [
                'name'     => 'Tivi',
                'icon'     => 'fa-solid fa-tv',
                'featured' => false,
                'desc'     => 'TV màn hình phẳng cỡ lớn với hàng trăm kênh trong nước và quốc tế, phim HD, thể thao trực tiếp và streaming.',
            ],
            [
                'name'     => 'Spa',
                'icon'     => 'fa-solid fa-spa',
                'featured' => false,
                'desc'     => 'Trải nghiệm liệu pháp thư giãn cao cấp: massage, chăm sóc da mặt, tắm khoáng và nhiều dịch vụ spa sang trọng khác.',
            ],
            [
                'name'     => 'Máy Sưởi',
                'icon'     => 'fa-solid fa-fire',
                'featured' => true,
                'desc'     => 'Hệ thống sưởi ấm cao cấp giữ nhiệt độ phòng lý tưởng trong mùa đông, đảm bảo sự thoải mái tuyệt đối.',
            ],
            [
                'name'     => 'Nóng Lạnh',
                'icon'     => 'fa-solid fa-faucet',
                'featured' => false,
                'desc'     => 'Hệ thống nước nóng lạnh tiêu chuẩn 5 sao, màn hình hiển thị nhiệt độ và điều chỉnh tự động, phục vụ 24/7.',
            ],
            [
                'name'     => 'Hồ Bơi',
                'icon'     => 'fa-solid fa-person-swimming',
                'featured' => false,
                'desc'     => 'Hồ bơi vô cực tầng thượng với tầm nhìn toàn cảnh thành phố. Mở cửa 6:00–22:00, phục vụ khăn và đồ uống tại hồ.',
            ],
            [
                'name'     => 'Bãi Đỗ Xe',
                'icon'     => 'fa-solid fa-square-parking',
                'featured' => true,
                'desc'     => 'Bãi đỗ xe rộng rãi, mái che và camera giám sát 24/7. Miễn phí cho khách lưu trú, hỗ trợ xe máy, ô tô và xe tải nhỏ.',
            ],
            [
                'name'     => 'Nhà Hàng',
                'icon'     => 'fa-solid fa-utensils',
                'featured' => false,
                'desc'     => 'Ẩm thực Á–Âu đa dạng, buffet sáng từ 6:30–10:00. Đầu bếp 5 sao với 15 năm kinh nghiệm đảm bảo chất lượng từng món.',
            ],
        ];
        ?>

        <div class="tn-amenities-grid">
            <?php foreach ($amenities as $item): ?>
            <div class="tn-amenity-card <?= $item['featured'] ? 'featured' : '' ?>">
                <div class="card-header">
                    <div class="icon-wrap">
                        <i class="<?= $item['icon'] ?>"></i>
                    </div>
                    <span class="card-name"><?= htmlspecialchars($item['name']) ?></span>
                </div>
                <p class="card-desc"><?= htmlspecialchars($item['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Thống kê -->
        <div class="tn-amenities-stats">
            <div class="tn-stat-item">
                <span class="num">9+</span>
                <span class="tn-stat-label">Tiện nghi</span>
            </div>
            <div class="tn-stat-item">
                <span class="num">24/7</span>
                <span class="tn-stat-label">Phục vụ</span>
            </div>
            <div class="tn-stat-item">
                <span class="num">100%</span>
                <span class="tn-stat-label">Hài lòng</span>
            </div>
            <div class="tn-stat-item">
                <span class="num">5★</span>
                <span class="tn-stat-label">Đánh giá</span>
            </div>
        </div>

    </div>
</section>

<!-- NÚT ĐIỀU HƯỚNG CUỐI TRANG -->
<div class="tn-back-section">
    <p>Sẵn sàng trải nghiệm những tiện nghi này?</p>
    <a href="?action=home"  class="tn-btn-home">← Về Trang Chủ</a>
    <a href="?action=rooms" class="tn-btn-rooms">Xem Danh Sách Phòng →</a>
</div>

</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
