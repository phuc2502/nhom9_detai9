<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>
<?php require_once ROOT_PATH . '/app/views/data.php'; ?>
<?php
/** @var \App\Models\Room[] $rooms — được truyền từ public/index.php (case 'home') */
$rooms = $rooms ?? [];

$today   = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+1 year'));
$checkinVal  = htmlspecialchars($_GET['checkin']  ?? '');
$checkoutVal = htmlspecialchars($_GET['checkout'] ?? '');
?>

<main>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>

    <div class="hero-content">
        <h1>Chào mừng đến với LuxStay Hotel</h1>
        <p>Không gian nghỉ dưỡng sang trọng, dịch vụ tận tâm.</p>
    </div>

    <!-- Form tìm kiếm -->
    <form class="search-box" method="GET" action="?" id="search-form" onsubmit="return validateDates()">
        <input type="hidden" name="action" value="rooms">

        <div class="search-row">

            <!-- Ngày nhận phòng -->
            <div class="search-field">
                <span class="field-icon">📅</span>
                <div class="field-text">
                    <small>Ngày nhận phòng</small>
                    <input type="date" name="checkin" id="checkin"
                           min="<?= $today ?>"
                           max="<?= $maxDate ?>"
                           value="<?= $checkinVal ?>"
                           onchange="enforceCheckin()"
                           required>
                </div>
            </div>

            <span class="search-divider">|</span>

            <!-- Ngày trả phòng -->
            <div class="search-field">
                <span class="field-icon">📅</span>
                <div class="field-text">
                    <small>Ngày trả phòng</small>
                    <input type="date" name="checkout" id="checkout"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           max="<?= $maxDate ?>"
                           value="<?= $checkoutVal ?>"
                           required>
                </div>
            </div>

            <span class="search-divider">|</span>

            <!-- Người lớn -->
            <div class="search-field">
                <span class="field-icon">👤</span>
                <div class="field-text">
                    <small>Người lớn</small>
                    <input type="number" name="adults"
                           min="1" max="10"
                           value="<?= (int)($_GET['adults'] ?? 2) ?>"
                           style="border:none;outline:none;font-family:inherit;font-size:.95rem;font-weight:600;color:#333;background:transparent;width:60px;"
                           required>
                </div>
            </div>

            <span class="search-divider">|</span>

            <!-- Trẻ em -->
            <div class="search-field">
                <span class="field-icon">🐣</span>
                <div class="field-text">
                    <small>Trẻ em</small>
                    <input type="number" name="children"
                           min="0" max="10"
                           value="<?= (int)($_GET['children'] ?? 0) ?>"
                           style="border:none;outline:none;font-family:inherit;font-size:.95rem;font-weight:600;color:#333;background:transparent;width:60px;">
                </div>
            </div>

            <button type="submit" class="btn-search">Tìm phòng</button>
        </div>
    </form>

    <script>
    var TODAY = '<?= $today ?>';
    var MAX_DATE = '<?= $maxDate ?>';

    function getTodayStr() {
        // Lấy ngày hôm nay theo giờ LOCAL của client (tránh lệch UTC)
        var d = new Date();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    function addDays(dateStr, n) {
        var d = new Date(dateStr + 'T00:00:00');
        d.setDate(d.getDate() + n);
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    function addYears(dateStr, n) {
        var d = new Date(dateStr + 'T00:00:00');
        d.setFullYear(d.getFullYear() + n);
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    function enforceCheckin() {
        var cin = document.getElementById('checkin');
        var today = getTodayStr();
        // Nếu chọn ngày quá khứ → reset về hôm nay
        if (cin.value && cin.value < today) {
            cin.value = today;
        }
        syncCheckout();
    }

    function syncCheckout() {
        var cin  = document.getElementById('checkin');
        var cout = document.getElementById('checkout');
        if (!cin.value) return;

        var minCout = addDays(cin.value, 1);
        var maxCout = addYears(cin.value, 1);

        cin.setAttribute('min', getTodayStr());
        cin.setAttribute('max', maxCout);
        cout.setAttribute('min', minCout);
        cout.setAttribute('max', maxCout);

        // Reset checkout nếu không còn hợp lệ
        if (cout.value && (cout.value <= cin.value || cout.value > maxCout)) {
            cout.value = '';
        }
    }

    function enforceCheckout() {
        var cin  = document.getElementById('checkin');
        var cout = document.getElementById('checkout');
        if (!cin.value || !cout.value) return;
        var minCout = addDays(cin.value, 1);
        if (cout.value <= cin.value || cout.value < minCout) {
            cout.value = '';
        }
    }

    function validateDates() {
        var cinVal  = document.getElementById('checkin').value;
        var coutVal = document.getElementById('checkout').value;
        var today   = getTodayStr();

        if (!cinVal) {
            alert('Vui lòng chọn ngày nhận phòng!');
            return false;
        }
        if (!coutVal) {
            alert('Vui lòng chọn ngày trả phòng!');
            return false;
        }
        if (cinVal < today) {
            alert('Ngày nhận phòng không được là ngày trong quá khứ!');
            document.getElementById('checkin').value = today;
            return false;
        }
        if (coutVal <= cinVal) {
            alert('Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 ngày!');
            return false;
        }
        if (coutVal > addYears(cinVal, 1)) {
            alert('Thời gian lưu trú tối đa là 1 năm!');
            return false;
        }
        return true;
    }

    window.addEventListener('DOMContentLoaded', function () {
        var cin  = document.getElementById('checkin');
        var cout = document.getElementById('checkout');
        var today = getTodayStr();

        // Set min/max ban đầu
        cin.setAttribute('min', today);
        cin.setAttribute('max', MAX_DATE);
        cout.setAttribute('min', addDays(today, 1));
        cout.setAttribute('max', MAX_DATE);

        // Bắt cả change lẫn input (gõ tay + dùng picker)
        cin.addEventListener('change', enforceCheckin);
        cin.addEventListener('input',  enforceCheckin);
        cout.addEventListener('change', enforceCheckout);
        cout.addEventListener('input',  enforceCheckout);

        // Đồng bộ nếu đã có giá trị (user bấm back)
        if (cin.value) syncCheckout();
    });
    </script>

</section>

    <!-- SECTION PHÒNG ĐỀ XUẤT - Lấy từ database -->
    <section class="section-rooms">
        <div class="container">
            <div class="section-header">
                <h2>Phòng đề xuất</h2>
            </div>

<style>
/* ---- Card nằm ngang cho trang chủ ---- */
.rooms-list-wrap-home {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}
.rooms-list-wrap-home .room-card-h {
  flex-direction: column;
  min-height: unset;
}
.rooms-list-wrap-home .rch-img-wrap {
  width: 100%;
  height: 200px;
}
.rooms-list-wrap-home .rch-price-col {
  width: 100%;
  border-left: none;
  border-top: 1px solid #f0f0f0;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  padding: 14px 16px;
}
.rooms-list-wrap-home .rch-price-wrap {
  flex: 1;
  text-align: left;
}
.rooms-list-wrap-home .rch-price { font-size: 1.2rem; }
.rooms-list-wrap-home .btn-book-h,
.rooms-list-wrap-home .btn-detail-h {
  flex: 1;
  min-width: 90px;
  padding: 9px 0;
  font-size: .85rem;
}
@media (max-width: 900px) {
  .rooms-list-wrap-home { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
  .rooms-list-wrap-home { grid-template-columns: 1fr; }
}
.room-card-h {
  display: flex;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0,0,0,.08);
  overflow: hidden;
  min-height: 220px;
}
.rch-img-wrap {
  position: relative;
  width: 42%;
  flex-shrink: 0;
}
.rch-img-wrap img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}
.rch-badge-avail {
  position: absolute; top: 12px; left: 12px;
  background: #10b981; color: #fff;
  font-size: .78rem; font-weight: 600;
  padding: 4px 10px; border-radius: 20px;
  z-index: 2;
}
.rch-floor {
  position: absolute; bottom: 10px; left: 12px;
  background: rgba(0,0,0,.55); color: #fff;
  font-size: .78rem; padding: 3px 9px; border-radius: 12px;
}
.rch-body {
  flex: 1; padding: 20px 22px; display: flex; flex-direction: column; gap: 10px;
}
.rch-title { font-size: 1.25rem; font-weight: 700; color: #1a1a1a; margin: 0; }
.rch-meta { display: flex; flex-direction: column; gap: 10px; }
.rch-meta-row { display: flex; align-items: flex-start; gap: 10px; }
.rch-meta-label {
  font-size: .7rem; font-weight: 700; color: #aaa;
  letter-spacing: .06em; min-width: 90px; padding-top: 3px;
}
.rch-chip {
  display: inline-block; background: #f3f4f6;
  color: #374151; font-size: .8rem;
  padding: 3px 11px; border-radius: 20px; border: 1px solid #e5e7eb;
}
.rch-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.rch-guests { font-size: .85rem; color: #444; white-space: nowrap; }
.rch-price-col {
  width: 190px; flex-shrink: 0;
  display: flex; flex-direction: column;
  align-items: stretch; justify-content: center;
  padding: 20px 18px; gap: 10px;
  border-left: 1px solid #f0f0f0;
}
.rch-price-wrap { text-align: right; }
.rch-price {
  display: block; font-size: 1.5rem; font-weight: 700; color: #1a1a1a;
}
.rch-price-unit {
  display: flex; align-items: center; justify-content: flex-end;
  gap: 4px; font-size: .78rem; color: #888;
  margin-top: 2px; white-space: nowrap;
}
.btn-book-h {
  display: block; text-align: center;
  background: #10b981; color: #fff;
  border-radius: 10px; padding: 11px 0;
  font-weight: 700; font-size: .93rem;
  text-decoration: none; transition: background .2s;
}
.btn-book-h:hover { background: #059669; }
.btn-detail-h {
  display: block; text-align: center;
  border: 1.5px solid #d1d5db; color: #374151;
  border-radius: 10px; padding: 10px 0;
  font-weight: 600; font-size: .9rem;
  text-decoration: none; transition: border-color .2s;
}
.btn-detail-h:hover { border-color: #10b981; color: #10b981; }
@media (max-width: 720px) {
  .room-card-h { flex-direction: column; }
  .rch-img-wrap { width: 100%; height: 200px; }
  .rch-price-col {
    width: 100%; border-left: none; border-top: 1px solid #f0f0f0;
    flex-direction: row; flex-wrap: wrap; align-items: center;
  }
  .rch-price-wrap { flex: 1; }
}
</style>

            <div class="rooms-list-wrap-home">

<?php
$featuredRooms = array_slice($rooms, 0, 3);
?>

<?php if (!empty($featuredRooms)): ?>
    <?php foreach ($featuredRooms as $room): ?>
    <div class="room-card-h">

        <div class="rch-img-wrap">
            <span class="rch-badge-avail"><?= $room->isActive() ? 'Còn phòng' : 'Bảo trì' ?></span>
            <img src="<?= getRoomImageUrl($room->getType(), 560, 340) ?>"
                 alt="<?= htmlspecialchars($room->getType()) ?>">
            <span class="rch-floor">Tầng <?= htmlspecialchars($room->getRoomNumber())[0] ?? '1' ?></span>
        </div>

        <div class="rch-body">
            <h3 class="rch-title">Phòng <?= htmlspecialchars($room->getType()) ?></h3>
            <div class="rch-meta">
                <div class="rch-meta-row">
                    <span class="rch-meta-label">CƠ SỞ</span>
                    <span class="rch-chip">Phòng Ngủ</span>
                </div>
                <?php $ams = $room->getAmenities(); if (!empty($ams)): ?>
                <div class="rch-meta-row">
                    <span class="rch-meta-label">TIỆN NGHI</span>
                    <div class="rch-chips">
                        <?php foreach (array_slice($ams, 0, 4) as $am): ?>
                            <span class="rch-chip"><?= htmlspecialchars($am) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="rch-meta-row">
                    <span class="rch-meta-label">KHÁCH HÀNG</span>
                    <span class="rch-guests">
                        👤 <?= $room->getMaxAdults() ?> Người Lớn &nbsp;
                        🐣 <?= $room->getMaxChildren() ?> Trẻ Em
                    </span>
                </div>
            </div>
        </div>

        <div class="rch-price-col">
            <div class="rch-price-wrap">
                <span class="rch-price"><?= number_format($room->getPricePerNight(), 0, ',', '.') ?></span>
                <span class="rch-price-unit">
                    <span>vnd mỗi đêm</span>
                </span>
            </div>
            <a href="?action=booking&room_id=<?= $room->getId() ?>" class="btn-book-h">Đặt Ngay</a>
            <a href="?action=room-detail&room_id=<?= $room->getId() ?>" class="btn-detail-h">Chi Tiết</a>
        </div>

    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="text-align:center; padding:40px; color:#888;">
        <p>Hiện chưa có phòng nào. Vui lòng quay lại sau.</p>
    </div>
<?php endif; ?>

            </div>

            <div class="center-btn">
                <a href="?action=rooms" class="btn-more">Xem thêm</a>
            </div>
        </div>
    </section>

    <!-- SECTION TIỆN NGHI -->
    <section class="section-amenities">
        <div class="container">
            <h2>Tiện nghi nổi bật</h2>
            <div class="amenities-grid">
                <div class="amenity-item"><div class="amenity-icon">🏊</div><p>Bể bơi ngoài trời</p></div>
                <div class="amenity-item"><div class="amenity-icon">🍹</div><p>Minibar</p></div>
                <div class="amenity-item"><div class="amenity-icon">🛎️</div><p>Butler 24/7</p></div>
                <div class="amenity-item"><div class="amenity-icon">🌊</div><p>Ban công / View biển</p></div>
                <div class="amenity-item"><div class="amenity-icon">🛋️</div><p>Phòng khách riêng</p></div>
            </div>
        </div>
    </section>

    <!-- SECTION GALLERY -->
    <section class="section-gallery">
        <div class="container">
            <h2>Thư viện ảnh</h2>
            <p class="section-sub">Khám phá không gian LuxStay Hotel</p>
            <div class="gallery-grid">
                <div class="gallery-item big">
                    <img src="<?= getRoomImageUrl('Deluxe', 800, 420) ?>" alt="Ảnh lớn">
                    <div class="gallery-label">
                        <strong>Phòng Deluxe</strong>
                        <span>Không gian sang trọng</span>
                    </div>
                </div>
                <div class="gallery-col">
                    <div class="gallery-item"><img src="<?= getRoomImageUrl('Suite', 400, 130) ?>" alt="Suite"></div>
                    <div class="gallery-item"><img src="<?= getRoomImageUrl('Penthouse', 400, 130) ?>" alt="Penthouse"></div>
                    <div class="gallery-item"><img src="<?= getRoomImageUrl('Luxury Sea View', 400, 130) ?>" alt="Sea View"></div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
