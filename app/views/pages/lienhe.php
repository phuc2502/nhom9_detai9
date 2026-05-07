<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<style>
    .lh-page-header {
        text-align: center;
        padding: 120px 24px 40px;
        background: #f5f7fb;
    }
    .lh-page-header h1 {
        font-family: 'Montserrat', 'Segoe UI', sans-serif;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #1a1a2e;
        line-height: 1.4;
    }
    .lh-underline {
        width: 60px; height: 3px;
        background: #2cbfae;
        margin: 12px auto 8px;
        border-radius: 2px;
    }
    .lh-page-header p { color: #777; font-size: 15px; }

    .lh-wrapper {
        max-width: 1100px;
        margin: 44px auto 64px;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .lh-card, .lh-form-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .lh-map {
        width: 100%; height: 250px;
        border: none;
        border-radius: 10px 10px 0 0;
        display: block;
    }

    .lh-info-body { padding: 22px 24px; }
    .lh-info-section { margin-bottom: 16px; }
    .lh-info-section:last-child { margin-bottom: 0; }
    .lh-info-section h3 {
        font-size: 11px;
        color: #2cbfae;
        margin: 0 0 4px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 700;
    }
    .lh-info-row { font-size: 14px; color: #444; }
    .lh-info-row a { color: #444; text-decoration: none; }
    .lh-info-row a:hover { color: #2cbfae; }

    .lh-form-card { padding: 30px; }
    .lh-form-card h2 {
        font-size: 1.4rem;
        color: #1a1a2e;
        margin: 0 0 22px;
        font-weight: 600;
    }
    .lh-form-group { margin-bottom: 15px; }
    .lh-form-group label {
        display: block;
        font-size: 13px;
        color: #555;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .lh-form-group input,
    .lh-form-group textarea {
        width: 100%;
        padding: 10px 13px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .lh-form-group input:focus,
    .lh-form-group textarea:focus {
        outline: none;
        border-color: #2cbfae;
    }
    .lh-form-group textarea { resize: vertical; min-height: 110px; }

    .lh-btn-submit {
        width: 100%; padding: 12px;
        background: #2cbfae; color: #fff;
        border: none; border-radius: 6px;
        cursor: pointer; font-size: 15px;
        font-weight: 600; font-family: inherit;
        transition: background 0.2s; margin-top: 4px;
        letter-spacing: 0.5px;
    }
    .lh-btn-submit:hover { background: #1ea898; }

    .lh-alert {
        padding: 11px 15px;
        margin-bottom: 16px;
        border-radius: 6px;
        font-size: 14px;
    }
    .lh-alert-success {
        background: #e6f9f6; color: #1b5e20;
        border-left: 4px solid #2cbfae;
    }
    .lh-alert-error {
        background: #fdecea; color: #c62828;
        border-left: 4px solid #e53935;
    }

    @media (max-width: 768px) {
        .lh-wrapper { grid-template-columns: 1fr; }
    }
</style>

<main>

<!-- HEADER -->
<div class="lh-page-header">
    <h1>Liên Hệ Chúng Tôi</h1>
    <div class="lh-underline"></div>
    <p>Liên hệ và góp ý với chúng tôi — chúng tôi luôn lắng nghe</p>
</div>

<!-- CONTENT -->
<div class="lh-wrapper">

    <!-- CỘT TRÁI: Bản đồ + thông tin liên hệ -->
    <div class="lh-card">
        <iframe class="lh-map"
            src="https://www.google.com/maps?q=246+Minh+Khai+Hai+Ba+Trung+Ha+Noi&output=embed"
            allowfullscreen loading="lazy">
        </iframe>
        <div class="lh-info-body">
            <div class="lh-info-section">
                <h3>📍 Địa chỉ</h3>
                <div class="lh-info-row">246 Minh Khai, Hai Bà Trưng, Hà Nội</div>
            </div>
            <div class="lh-info-section">
                <h3>📞 Hotline</h3>
                <div class="lh-info-row">
                    <a href="tel:02363000000">0236 300 0000</a>
                </div>
            </div>
            <div class="lh-info-section">
                <h3>✉️ Email</h3>
                <div class="lh-info-row">
                    <a href="mailto:info@luxstay.vn">info@luxstay.vn</a>
                </div>
            </div>
            <div class="lh-info-section">
                <h3>🕐 Giờ làm việc</h3>
                <div class="lh-info-row">Lễ tân 24/7 · Hỗ trợ: 7:00 – 22:00</div>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: Form gửi tin nhắn -->
    <div class="lh-form-card">
        <h2>Gửi Tin Nhắn</h2>

        <?php if (!empty($contactSuccess)): ?>
            <div class="lh-alert lh-alert-success">
                ✅ Cảm ơn bạn! Tin nhắn đã được gửi thành công. Chúng tôi sẽ phản hồi sớm nhất.
            </div>
        <?php endif; ?>
        <?php if (!empty($contactError)): ?>
            <div class="lh-alert lh-alert-error">
                ❌ <?= htmlspecialchars($contactError) ?>
            </div>
        <?php endif; ?>

        <form action="?action=contact" method="post">
            <div class="lh-form-group">
                <label>Họ và tên</label>
                <input type="text" name="name"
                       placeholder="Nguyễn Văn A"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="lh-form-group">
                <label>Email</label>
                <input type="email" name="email"
                       placeholder="email@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="lh-form-group">
                <label>Nội dung tin nhắn</label>
                <textarea name="message"
                          placeholder="Nhập nội dung bạn muốn gửi..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="lh-btn-submit">Gửi Tin Nhắn →</button>
        </form>
    </div>

</div>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
