<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>

<main>
    <section class="section-amenities">
        <div class="container">
            <h2>Liên Hệ Với LuxStay Hotel</h2>

            <div style="max-width:800px; margin:0 auto; text-align:left; line-height:1.8; font-size:1rem; color:#444;">
                <p>
                    Nếu bạn có bất kỳ thắc mắc hoặc yêu cầu nào, vui lòng liên hệ với chúng tôi qua thông tin dưới đây
                    hoặc gửi tin nhắn trực tiếp bằng form liên hệ.
                </p>

                <!-- Thông tin liên hệ -->
                <ul style="list-style:none; padding:0; margin:20px 0;">
                    <li>📍 Địa chỉ: 246 Minh Khai, Hai Bà Trưng, Hà Nội</li>
                    <li>📞 Điện thoại: <a href="tel:02363000000">0236 300 0000</a></li>
                    <li>✉️ Email: <a href="mailto:info@luxstay.vn">info@luxstay.vn</a></li>
                </ul>

                <!-- ── THÊM MỚI: Hiện thông báo sau khi gửi form ── -->
                <!-- Biến $contactSuccess và $contactError được truyền từ index.php case 'contact' -->
                <?php if (!empty($contactSuccess)): ?>
                    <div style="background:#e8f5e9; color:#1b5e20; padding:14px 18px; border-radius:8px; margin-bottom:20px; border-left:4px solid #2e7d32;">
                        ✅ Cảm ơn bạn! Tin nhắn đã được gửi thành công. Chúng tôi sẽ phản hồi sớm nhất.
                    </div>
                <?php endif; ?>

                <?php if (!empty($contactError)): ?>
                    <div style="background:#fce4ec; color:#c62828; padding:14px 18px; border-radius:8px; margin-bottom:20px; border-left:4px solid #c62828;">
                        ❌ <?= htmlspecialchars($contactError) ?>
                    </div>
                <?php endif; ?>

                <!-- Form liên hệ -->
                <!-- SỬA: action="lienhe.php" → action="?action=contact" (đúng routing qua index.php) -->
                <form action="?action=contact" method="post" style="margin-top:20px;">
                    <div style="margin-bottom:15px;">
                        <label for="name">Họ và tên:</label><br>
                        <input type="text" id="name" name="name" required
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label for="email">Email:</label><br>
                        <input type="email" id="email" name="email" required
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label for="message">Nội dung:</label><br>
                        <textarea id="message" name="message" rows="5" required
                                  style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"></textarea>
                    </div>

                    <button type="submit" class="btn-book">Gửi liên hệ</button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
