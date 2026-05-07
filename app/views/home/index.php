<?php include ROOT_PATH . '/app/views/layout/header.php'; ?>
<?php require_once ROOT_PATH . '/app/views/data.php'; ?>

<main>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>

    <div class="hero-content">
        <h1>Chào mừng đến với LuxStay Hotel</h1>
        <p>Không gian nghỉ dưỡng sang trọng, dịch vụ tận tâm.</p>
    </div>

    <div class="search-box">
        <div class="search-row">
            <div class="search-field date-field" id="date-field" onclick="toggleCalendar()">
                <span class="field-icon">📅</span>
                <div class="field-text">
                    <small>Ngày nhận — Ngày trả</small>
                    <span id="date-label">Chọn ngày</span>
                </div>
            </div>
            <span class="search-divider">|</span>
            <div class="search-field" id="guest-field" onclick="toggleGuests()">
                <span class="field-icon">👤</span>
                <div class="field-text">
                    <small>Khách</small>
                    <span id="guest-label">2 người lớn · 0 trẻ em</span>
                </div>
            </div>
            <button class="btn-search" onclick="doSearch()">Tìm phòng</button>
        </div>

        <div class="calendar-popup" id="calendar-popup">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 12px 2px;">
                <button onclick="prevMonth()" title="Tháng trước"
                        style="background:none; border:none; font-size:1.3rem; cursor:pointer;
                               color:#009688; padding:4px 10px; border-radius:6px; line-height:1;"
                        onmouseover="this.style.background='#e0f7f4'"
                        onmouseout="this.style.background='none'">&#8249;</button>
                <span id="cal-nav-label" style="font-size:.85rem; color:#555; font-weight:500;"></span>
                <button onclick="nextMonth()" title="Tháng sau"
                        style="background:none; border:none; font-size:1.3rem; cursor:pointer;
                               color:#009688; padding:4px 10px; border-radius:6px; line-height:1;"
                        onmouseover="this.style.background='#e0f7f4'"
                        onmouseout="this.style.background='none'">&#8250;</button>
            </div>
            <div class="calendar-months">
                <div class="cal-month" id="cal-left"></div>
                <div class="cal-month" id="cal-right"></div>
            </div>
            <div class="cal-footer">
                <button class="cal-clear" onclick="clearDates()">Xóa ngày</button>
            </div>
        </div>

        <div class="guest-box" id="guest-box">
            <div class="guest-row">
                <div><strong>Người lớn</strong></div>
                <div class="counter">
                    <button onclick="changeGuest('adults',-1)">−</button>
                    <span id="adults-count">2</span>
                    <button onclick="changeGuest('adults',1)">+</button>
                </div>
            </div>
            <div class="guest-row">
                <div>
                    <strong>Trẻ em</strong>
                    <small style="display:block;color:#888;">Dưới 18 tuổi</small>
                </div>
                <div class="counter">
                    <button onclick="changeGuest('children',-1)">−</button>
                    <span id="children-count">0</span>
                    <button onclick="changeGuest('children',1)">+</button>
                </div>
            </div>
            <button class="btn-done" onclick="toggleGuests()">Xong</button>
        </div>
    </div>

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
// Lấy tối đa 3 phòng active từ danh sách đã truy vấn DB
$featuredRooms = array_slice($rooms, 0, 3);
?>

<?php if (!empty($featuredRooms)): ?>
    <?php foreach ($featuredRooms as $room): ?>
    <div class="room-card-h">

        <!-- Ảnh phòng -->
        <div class="rch-img-wrap">
            <span class="rch-badge-avail"><?= $room->isActive() ? 'Còn phòng' : 'Bảo trì' ?></span>
            <img src="<?= getRoomImageUrl($room->getType(), 560, 340) ?>"
                 alt="<?= htmlspecialchars($room->getType()) ?>">
            <span class="rch-floor">Tầng <?= htmlspecialchars($room->getRoomNumber())[0] ?? '1' ?></span>
        </div>

        <!-- Thông tin phòng -->
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

        <!-- Giá + nút -->
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
    <div class="amenity-item">
        <div class="amenity-icon">🏊</div>
        <p>Bể bơi ngoài trời</p>
    </div>
    <div class="amenity-item">
        <div class="amenity-icon">🍹</div>
        <p>Minibar</p>
    </div>
    <div class="amenity-item">
        <div class="amenity-icon">🛎️</div>
        <p>Butler 24/7</p>
    </div>
    <div class="amenity-item">
        <div class="amenity-icon">🌊</div>
        <p>Ban công / View biển</p>
    </div>
    <div class="amenity-item">
        <div class="amenity-icon">🛋️</div>
        <p>Phòng khách riêng</p>
    </div>
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
                    <div class="gallery-item">
                       <img src="<?= getRoomImageUrl('Suite', 400, 130) ?>" alt="Ảnh nhỏ">
                    </div>
                    <div class="gallery-item">
                        <img src="<?= getRoomImageUrl('Penthouse', 400, 130) ?>" alt="Ảnh nhỏ">
                    </div>
                    <div class="gallery-item">
                        <img src="<?= getRoomImageUrl('Luxury Sea View', 400, 130) ?>" alt="Ảnh nhỏ">
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>



<script>
// ===== STATE =====
let checkinDate  = null;
let checkoutDate = null;
let adults   = 2;
let children = 0;
let selecting = 'checkin'; // 'checkin' | 'checkout'

// ===== CALENDAR =====
function toggleCalendar() {
    const pop = document.getElementById('calendar-popup');
    const guestBox = document.getElementById('guest-box');
    guestBox.classList.remove('open');
    pop.classList.toggle('open');
    if (pop.classList.contains('open')) renderCalendars();
}

function toggleGuests() {
    const guestBox = document.getElementById('guest-box');
    const pop = document.getElementById('calendar-popup');
    pop.classList.remove('open');
    guestBox.classList.toggle('open');
}

// Đóng popup khi click ngoài
document.addEventListener('click', function(e) {
    const cal   = document.getElementById('calendar-popup');
    const guest = document.getElementById('guest-box');

    if (!e.target.closest('#date-field') && !e.target.closest('#calendar-popup'))
        cal.classList.remove('open');

    if (!e.target.closest('#guest-field') && !e.target.closest('#guest-box'))
        guest.classList.remove('open');
});

let calYear  = new Date().getFullYear();
let calMonth = new Date().getMonth(); // 0-indexed

function renderCalendars() {
    renderMonth('cal-left',  calYear, calMonth);
    let ny = calMonth === 11 ? calYear + 1 : calYear;
    let nm = calMonth === 11 ? 0 : calMonth + 1;
    renderMonth('cal-right', ny, nm);

    var monthNames = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6',
                      'Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
    var lbl = document.getElementById('cal-nav-label');
    if (lbl) lbl.textContent = monthNames[calMonth] + ' ' + calYear
                             + ' — ' + monthNames[nm] + ' ' + ny;
}

function prevMonth() {
    if (calMonth === 0) { calMonth = 11; calYear--; }
    else { calMonth--; }
    renderCalendars();
}

function nextMonth() {
    if (calMonth === 11) { calMonth = 0; calYear++; }
    else { calMonth++; }
    renderCalendars();
}

function renderMonth(elId, year, month) {
    const el = document.getElementById(elId);
    const monthNames = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6',
                        'Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
    const today = new Date(); today.setHours(0,0,0,0);

    let html = `<h4>${monthNames[month]} ${year}</h4><div class="cal-grid">`;
    ['T2','T3','T4','T5','T6','T7','CN'].forEach(d => {
        html += `<div class="day-name">${d}</div>`;
    });

    const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
    const offset = firstDay === 0 ? 6 : firstDay - 1;
    for (let i = 0; i < offset; i++) html += '<div></div>';

    const daysInMonth = new Date(year, month + 1, 0).getDate();
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        date.setHours(0,0,0,0);
        const ds = dateStr(date);
        let cls = 'day';
        if (date < today) cls += ' disabled';
        if (checkinDate && ds === dateStr(checkinDate)) cls += ' selected';
        if (checkoutDate && ds === dateStr(checkoutDate)) cls += ' selected';
        if (checkinDate && checkoutDate && date > checkinDate && date < checkoutDate) cls += ' in-range';
        const disabled = date < today ? 'disabled' : '';
        html += `<button class="${cls}" ${disabled} onclick="selectDay(${year},${month},${d})">${d}</button>`;
    }
    html += '</div>';
    el.innerHTML = html;
}

function selectDay(y, m, d) {
    const date = new Date(y, m, d); date.setHours(0,0,0,0);
    if (selecting === 'checkin' || (checkinDate && date <= checkinDate)) {
        checkinDate  = date;
        checkoutDate = null;
        selecting = 'checkout';
    } else {
        checkoutDate = date;
        selecting = 'checkin';
        document.getElementById('calendar-popup').classList.remove('open');
    }
    updateDateLabel();
    renderCalendars();
}

function clearDates() {
    checkinDate = checkoutDate = null;
    selecting = 'checkin';
    updateDateLabel();
    renderCalendars();
}

function dateStr(d) {
    var yyyy = d.getFullYear();
    var mm   = String(d.getMonth() + 1).padStart(2, '0');
    var dd   = String(d.getDate()).padStart(2, '0');
    return yyyy + '-' + mm + '-' + dd;
}

function formatVN(d) {
    return d.getDate() + '/' + (d.getMonth()+1) + '/' + d.getFullYear();
}

function updateDateLabel() {
    const el = document.getElementById('date-label');
    if (checkinDate && checkoutDate)
        el.textContent = formatVN(checkinDate) + ' — ' + formatVN(checkoutDate);
    else if (checkinDate)
        el.textContent = formatVN(checkinDate) + ' — Chọn ngày trả';
    else
        el.textContent = 'Chọn ngày';
}

// ===== GUESTS =====
function changeGuest(type, delta) {
    if (type === 'adults') {
        adults = Math.max(1, adults + delta);
        document.getElementById('adults-count').textContent = adults;
    } else {
        children = Math.max(0, children + delta);
        document.getElementById('children-count').textContent = children;
    }
    document.getElementById('guest-label').textContent =
        adults + ' người lớn · ' + children + ' trẻ em';
}

// ===== TÌM PHÒNG =====
function doSearch() {
    if (!checkinDate || !checkoutDate) {
        alert('Vui lòng chọn ngày nhận và ngày trả phòng!');
        return;
    }
    const params = new URLSearchParams({
        action:   'rooms',
        checkin:  dateStr(checkinDate),
        checkout: dateStr(checkoutDate),
        adults:   adults,
        children: children
    });
    window.location.href = '?' + params.toString();
}
</script>
<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>
