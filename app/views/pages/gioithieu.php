<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<style>
    /* ===== TRANG GIỚI THIỆU ===== */
    .about-page {
        padding-top: 68px;
        background: #fff;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    /* ---- TIÊU ĐỀ TRANG ---- */
    .about-heading {
        text-align: center;
        padding: 60px 20px 40px;
    }
    .about-heading h1 {
        font-family: 'Times New Roman', serif;
        font-size: 2.4rem;
        font-style: italic;
        letter-spacing: 4px;
        color: #1a1a2e;
        text-transform: uppercase;
        margin-bottom: 12px;
    }
    .about-heading .heading-line {
        width: 60px; height: 3px;
        background: var(--xanh, #009688);
        margin: 0 auto 20px; border-radius: 2px;
    }
    .about-heading p {
        max-width: 600px; margin: 0 auto;
        color: #555; font-size: 0.95rem; line-height: 1.8;
    }

    /* ---- PHẦN 1: GIỚI THIỆU (text trái, ảnh phải) ---- */
    .ab-intro-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        max-width: 1100px;
        margin: 0 auto 70px;
        padding: 0 40px;
    }
    @media(max-width:768px) {
        .ab-intro-section { grid-template-columns: 1fr; gap: 30px; }
    }
    .ab-intro-text h2 {
        font-family: 'Times New Roman', serif;
        font-size: 1.7rem; color: #1a1a2e; margin-bottom: 16px;
    }
    .ab-intro-text p {
        font-size: 0.95rem; color: #555;
        line-height: 1.85; margin-bottom: 12px;
    }
    .ab-intro-img {
        border-radius: 8px; overflow: hidden; height: 300px;
    }
    .ab-intro-img img {
        width: 100%; height: 100%; object-fit: cover;
    }

    /* ---- PHẦN 2: CON SỐ NỔI BẬT ---- */
    .ab-stats-section {
        background: #f7f7f7; padding: 50px 40px;
    }
    .ab-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        max-width: 1100px; margin: 0 auto;
    }
    @media(max-width:768px) {
        .ab-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .ab-stat-item {
        text-align: center; padding: 30px 20px;
        border-right: 1px solid #e0e0e0;
    }
    .ab-stat-item:last-child { border-right: none; }
    .ab-stat-item::before {
        content: '';
        display: block; width: 50px; height: 4px;
        background: var(--xanh, #009688);
        margin: 0 auto 16px; border-radius: 2px;
    }
    .ab-stat-number {
        font-family: 'Times New Roman', serif;
        font-size: 2.4rem; font-weight: 600;
        color: #1a1a2e; line-height: 1; margin-bottom: 6px;
    }
    .ab-stat-label {
        font-size: 0.85rem; color: #888;
        font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
    }

    /* ---- PHẦN 3: GIÁ TRỊ CỐT LÕI ---- */
    .ab-values-section {
        padding: 70px 40px;
        max-width: 1100px; margin: 0 auto;
    }
    .ab-values-section h2 {
        font-family: 'Times New Roman', serif;
        font-size: 1.7rem; color: #1a1a2e;
        text-align: center; margin-bottom: 40px;
    }
    .ab-values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }
    @media(max-width:768px) { .ab-values-grid { grid-template-columns: 1fr; } }
    .ab-value-card {
        border: 1px solid #eee; border-radius: 10px;
        padding: 32px 24px; text-align: center;
        transition: box-shadow 0.3s, transform 0.3s;
    }
    .ab-value-card:hover {
        box-shadow: 0 8px 28px rgba(0,0,0,0.1);
        transform: translateY(-4px);
    }
    .ab-value-icon { font-size: 2.4rem; margin-bottom: 16px; }
    .ab-value-card h3 {
        font-family: 'Times New Roman', serif;
        font-size: 1.15rem; color: #1a1a2e; margin-bottom: 10px;
    }
    .ab-value-card p { font-size: 0.88rem; color: #777; line-height: 1.75; }

    /* ---- PHẦN 4: TẦM NHÌN & SỨ MỆNH (ảnh trái, text phải) ---- */
    .ab-history-section { background: #f7f7f7; padding: 70px 40px; }
    .ab-history-inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px; align-items: center;
        max-width: 1100px; margin: 0 auto;
    }
    @media(max-width:768px) { .ab-history-inner { grid-template-columns: 1fr; } }
    .ab-history-img {
        border-radius: 8px; overflow: hidden; height: 320px; order: -1;
    }
    .ab-history-img img { width: 100%; height: 100%; object-fit: cover; }
    .ab-history-text h2 {
        font-family: 'Times New Roman', serif;
        font-size: 1.7rem; color: #1a1a2e; margin-bottom: 16px;
    }
    .ab-history-text p {
        font-size: 0.95rem; color: #555;
        line-height: 1.85; margin-bottom: 14px;
    }
    .ab-check-list { list-style: none; padding: 0; margin-top: 16px; }
    .ab-check-list li {
        display: flex; align-items: flex-start;
        gap: 10px; font-size: 0.92rem;
        color: #444; margin-bottom: 10px;
    }
    .ab-check-list li::before {
        content: '✓';
        color: var(--xanh, #009688);
        font-weight: 700; font-size: 1rem;
        flex-shrink: 0; margin-top: 1px;
    }

    /* ---- PHẦN 5: CTA ---- */
    .ab-cta-section {
        background: #1a1a2e; color: #fff;
        text-align: center; padding: 70px 40px;
    }
    .ab-cta-section h2 {
        font-family: 'Times New Roman', serif;
        font-size: 1.9rem; margin-bottom: 12px;
    }
    .ab-cta-section p {
        color: rgba(255,255,255,0.7); font-size: 0.95rem; margin-bottom: 28px;
    }
    .ab-cta-btns {
        display: flex; gap: 14px;
        justify-content: center; flex-wrap: wrap;
    }
    .ab-btn-main {
        background: var(--xanh, #009688); color: #fff;
        padding: 13px 32px; border-radius: 8px;
        font-weight: 700; font-size: 0.95rem;
        text-decoration: none; transition: opacity 0.2s;
    }
    .ab-btn-main:hover { opacity: 0.85; }
    .ab-btn-outline {
        border: 1px solid rgba(255,255,255,0.4); color: #fff;
        padding: 13px 32px; border-radius: 8px;
        font-size: 0.95rem; text-decoration: none; transition: border-color 0.2s;
    }
    .ab-btn-outline:hover {
        border-color: var(--xanh, #009688);
        color: var(--xanh, #009688);
    }
</style>

<main>
<div class="about-page">

    <!-- TIÊU ĐỀ TRANG -->
    <div class="about-heading">
        <h1>Giới Thiệu</h1>
        <div class="heading-line"></div>
        <p>LuxStay Hotel cam kết mang đến những trải nghiệm tuyệt vời nhất trong không gian sang trọng và đẳng cấp.</p>
    </div>

    <!-- PHẦN 1: GIỚI THIỆU KHÁCH SẠN -->
    <div class="ab-intro-section">
        <div class="ab-intro-text">
            <h2>Khách Sạn LuxStay</h2>
            <p>LuxStay Hotel là một trong những khách sạn hàng đầu tại Việt Nam, được thiết kế theo phong cách hiện đại với hệ thống phòng nghỉ sang trọng, nhà hàng, quầy bar, trung tâm thể dục và spa.</p>
            <p>Khách sạn có hơn 10 tầng với hơn 50 phòng nghỉ rộng rãi, trang bị đầy đủ tiện nghi hiện đại, đảm bảo sự thoải mái tuyệt đối cho khách hàng trong suốt kỳ lưu trú.</p>
        </div>
        <div class="ab-intro-img">
            <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800&q=80" alt="LuxStay Hotel">
        </div>
    </div>

    <!-- PHẦN 2: CON SỐ NỔI BẬT -->
    <div class="ab-stats-section">
        <div class="ab-stats-grid">
            <div class="ab-stat-item">
                <div class="ab-stat-number">50+</div>
                <div class="ab-stat-label">Phòng nghỉ</div>
            </div>
            <div class="ab-stat-item">
                <div class="ab-stat-number">10+</div>
                <div class="ab-stat-label">Năm kinh nghiệm</div>
            </div>
            <div class="ab-stat-item">
                <div class="ab-stat-number">5★</div>
                <div class="ab-stat-label">Chất lượng dịch vụ</div>
            </div>
            <div class="ab-stat-item">
                <div class="ab-stat-number">2K+</div>
                <div class="ab-stat-label">Khách hài lòng</div>
            </div>
        </div>
    </div>

    <!-- PHẦN 3: GIÁ TRỊ CỐT LÕI -->
    <div class="ab-values-section">
        <h2>Giá Trị Cốt Lõi</h2>
        <div class="ab-values-grid">
            <div class="ab-value-card">
                <div class="ab-value-icon">🏆</div>
                <h3>Chất Lượng Vượt Trội</h3>
                <p>Mỗi phòng nghỉ được trang bị nội thất cao cấp, đảm bảo không gian thoải mái và sang trọng cho từng khách hàng.</p>
            </div>
            <div class="ab-value-card">
                <div class="ab-value-icon">❤️</div>
                <h3>Dịch Vụ Tận Tâm</h3>
                <p>Đội ngũ nhân viên chuyên nghiệp, thân thiện, sẵn sàng phục vụ 24/7 để đáp ứng mọi nhu cầu của quý khách.</p>
            </div>
            <div class="ab-value-card">
                <div class="ab-value-icon">🌿</div>
                <h3>Môi Trường Xanh</h3>
                <p>LuxStay cam kết phát triển bền vững, thân thiện với môi trường, mang lại không gian trong lành cho khách hàng.</p>
            </div>
        </div>
    </div>

    <!-- PHẦN 4: TẦM NHÌN & SỨ MỆNH -->
    <div class="ab-history-section">
        <div class="ab-history-inner">
            <div class="ab-history-img">
                <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80" alt="Tầm nhìn LuxStay">
            </div>
            <div class="ab-history-text">
                <h2>Tầm Nhìn & Sứ Mệnh</h2>
                <p>LuxStay được thành lập với mục tiêu trở thành khách sạn hàng đầu khu vực, nơi mỗi khoảnh khắc lưu trú là một trải nghiệm đáng nhớ.</p>
                <p>Chúng tôi không ngừng cải tiến dịch vụ, nâng cấp cơ sở vật chất để đáp ứng nhu cầu ngày càng cao của khách hàng trong và ngoài nước.</p>
                <ul class="ab-check-list">
                    <li>Hệ thống phòng nghỉ sang trọng, tiện nghi hiện đại</li>
                    <li>Nhà hàng phục vụ ẩm thực Á – Âu đa dạng</li>
                    <li>Trung tâm spa & thể dục cao cấp</li>
                    <li>Đội ngũ nhân viên được đào tạo chuyên nghiệp</li>
                    <li>Vị trí trung tâm, giao thông thuận tiện</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PHẦN 5: CTA -->
    <div class="ab-cta-section">
        <h2>Sẵn sàng trải nghiệm LuxStay?</h2>
        <p>Đặt phòng ngay hôm nay và nhận ưu đãi đặc biệt dành riêng cho bạn.</p>
        <div class="ab-cta-btns">
            <a href="?action=rooms"   class="ab-btn-main">Đặt Phòng Ngay</a>
            <a href="?action=contact" class="ab-btn-outline">Liên Hệ Chúng Tôi</a>
        </div>
    </div>

</div>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
