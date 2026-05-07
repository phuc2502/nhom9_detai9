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

            <div class="rooms-grid-wrap">
                <div class="rooms-grid">

<?php
// Lấy tối đa 3 phòng active từ danh sách đã truy vấn DB
$featuredRooms = array_slice($rooms, 0, 3);
?>

<?php if (!empty($featuredRooms)): ?>
    <?php foreach ($featuredRooms as $room): ?>
    <div class="room-card">
        <div class="card-img">
            <img src="<?= getRoomImageUrl($room->getType(), 400, 200) ?>"
                 alt="<?= htmlspecialchars($room->getType()) ?>">
        </div>
        <div class="card-body">
            <h3>Phòng <?= htmlspecialchars($room->getType()) ?></h3>
            <p class="card-price"><?= number_format($room->getPricePerNight(), 0, ',', '.') ?>đ / đêm</p>
            <div class="card-badges">
                <?php if ($room->isActive()): ?>
                    <span class="badge green">Còn phòng</span>
                <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#dc2626;">Bảo trì</span>
                <?php endif; ?>
            </div>
            <p class="card-guests">Tối đa <?= $room->getMaxGuests() ?> khách</p>
            <div class="card-actions">
                <a href="?action=booking&room_id=<?= $room->getId() ?>" class="btn-book">Đặt ngay</a>
                <a href="?action=room-detail&room_id=<?= $room->getId() ?>" class="btn-detail">Chi tiết</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="grid-column:1/-1; text-align:center; padding:40px; color:#888;">
        <p>Hiện chưa có phòng nào. Vui lòng quay lại sau.</p>
    </div>
<?php endif; ?>

                </div>
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
                    <div class="amenity-icon">🍽️</div>
                    <p>Nhà hàng sang trọng</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-icon">💪</div>
                    <p>Phòng gym hiện đại</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-icon">🛏️</div>
                    <p>Phòng nghỉ tiện nghi</p>
                </div>
                <div class="amenity-item">
                    <div class="amenity-icon">🚗</div>
                    <p>Dịch vụ đưa đón</p>
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
