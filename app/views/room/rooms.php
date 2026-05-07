<?php include ROOT_PATH . '/app/views/layout/header.php';
require_once ROOT_PATH . '/app/views/data.php';
?>

<main>
<section class="section-rooms">
<div class="container rooms-layout">

  <!-- ========== SIDEBAR BỘ LỌC ========== -->
  <aside class="filter-sidebar">
    <form method="GET" action="" id="filter-form">
      <input type="hidden" name="action" value="rooms">

      <div class="filter-block">
        <h3 class="filter-title">🗓 Ngày &amp; Khách</h3>
        <label>Nhận phòng</label>
        <input type="date" name="checkin"  value="<?= htmlspecialchars($filterCheckIn) ?>">
        <label>Trả phòng</label>
        <input type="date" name="checkout" value="<?= htmlspecialchars($filterCheckOut) ?>">
        <label>Người lớn</label>
        <input type="number" name="adults"   min="0" max="10" value="<?= $filterAdults ?>">
        <label>Trẻ em</label>
        <input type="number" name="children" min="0" max="10" value="<?= $filterChildren ?>">
      </div>

      <div class="filter-block">
        <h3 class="filter-title">💰 Giá / đêm (VNĐ)</h3>
        <div class="price-row">
          <input type="number" name="price_min" placeholder="Từ" min="0" step="50000"
                 value="<?= $filterPriceMin > 0 ? (int)$filterPriceMin : '' ?>">
          <span>–</span>
          <input type="number" name="price_max" placeholder="Đến" min="0" step="50000"
                 value="<?= $filterPriceMax > 0 ? (int)$filterPriceMax : '' ?>">
        </div>
        <div class="price-presets">
          <button type="button" class="preset" data-min="0"       data-max="500000">Dưới 500k</button>
          <button type="button" class="preset" data-min="500000"  data-max="1000000">500k–1tr</button>
          <button type="button" class="preset" data-min="1000000" data-max="3000000">1tr–3tr</button>
          <button type="button" class="preset" data-min="3000000" data-max="0">Trên 3tr</button>
        </div>
      </div>

      <div class="filter-block">
        <h3 class="filter-title">🏨 Loại phòng</h3>
        <select name="room_type">
          <option value="">-- Tất cả --</option>
          <?php foreach ($allTypes as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>>
              <?= htmlspecialchars($t) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-block">
        <h3 class="filter-title">✨ Tiện nghi</h3>
        <div class="amenities-list">
          <?php foreach ($allAmenities as $am): ?>
            <label class="am-check">
              <input type="checkbox" name="amenities[]" value="<?= htmlspecialchars($am) ?>"
                     <?= in_array($am, $filterAmenities, true) ? 'checked' : '' ?>>
              <?= htmlspecialchars($am) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="filter-block">
        <h3 class="filter-title">↕ Sắp xếp</h3>
        <select name="sort">
          <option value="price_asc"  <?= $filterSortBy==='price_asc'  ? 'selected':'' ?>>Giá tăng dần</option>
          <option value="price_desc" <?= $filterSortBy==='price_desc' ? 'selected':'' ?>>Giá giảm dần</option>
          <option value="guests_asc" <?= $filterSortBy==='guests_asc' ? 'selected':'' ?>>Số khách tăng dần</option>
          <option value="type_asc"   <?= $filterSortBy==='type_asc'   ? 'selected':'' ?>>Tên loại A→Z</option>
        </select>
      </div>

      <button type="submit" class="btn-apply">🔍 Áp dụng</button>
      <a href="?action=rooms" class="btn-clear">✕ Xóa bộ lọc</a>
    </form>
  </aside>

  <!-- ========== DANH SÁCH PHÒNG ========== -->
  <div class="rooms-content">

    <?php if (!empty($searchError)): ?>
      <div class="filter-notice error">
        <?= htmlspecialchars($searchError) ?>
        <a href="?action=rooms" class="clear-link">Xóa bộ lọc</a>
      </div>
    <?php elseif (!empty($searchNotice)): ?>
      <div class="filter-notice">
        <?= htmlspecialchars($searchNotice) ?> —
        <strong><?= $total ?></strong> phòng phù hợp.
        <a href="?action=rooms" class="clear-link">Xóa bộ lọc</a>
      </div>
    <?php endif; ?>

    <?php if (empty($rooms)): ?>
      <div class="empty-state">
        <h3>Không có phòng phù hợp</h3>
        <p>Hãy thử thay đổi bộ lọc để xem thêm lựa chọn.</p>
      </div>
    <?php else: ?>

      <!-- Topbar: số phòng + sắp xếp -->
      <div class="list-topbar">
        <span class="result-count">Hiển thị <strong><?= $total ?></strong> phòng</span>
        <select class="sort-select" name="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()">
          <option value="price_asc"  <?= $filterSortBy==='price_asc'  ? 'selected':'' ?>>Sắp xếp mặc định</option>
          <option value="price_desc" <?= $filterSortBy==='price_desc' ? 'selected':'' ?>>Giá giảm dần</option>
          <option value="guests_asc" <?= $filterSortBy==='guests_asc' ? 'selected':'' ?>>Số khách tăng</option>
          <option value="type_asc"   <?= $filterSortBy==='type_asc'   ? 'selected':'' ?>>Tên A→Z</option>
        </select>
      </div>

      <!-- Danh sách phòng nằm ngang -->
      <div class="rooms-list-wrap">
        <?php foreach ($rooms as $room): ?>
        <div class="room-card-h">

          <!-- Ảnh phòng -->
          <div class="rch-img-wrap">
            <span class="rch-badge-avail">Còn phòng</span>
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
                  👤 <?= $room->getMaxGuests() ?> Người Lớn &nbsp;
                  🐣 0 Trẻ Em
                </span>
              </div>
            </div>
          </div>

          <!-- Giá + nút -->
          <div class="rch-price-col">
            <div class="rch-price-wrap">
              <span class="rch-price"><?= number_format($room->getPricePerNight(), 0, ',', '.') ?></span>
              <span class="rch-price-unit"><span>vnd mỗi đêm</span>
<span class="rch-vat">Chưa VAT</span></span>
            </div>
            <a href="?action=booking&room_id=<?= $room->getId() ?>" class="btn-book-h">Đặt Ngay</a>
            <a href="?action=room-detail&room_id=<?= $room->getId() ?>" class="btn-detail-h">Chi Tiết</a>
          </div>

        </div>
        <?php endforeach; ?>
      </div><!-- /.rooms-list-wrap -->

    <?php endif; ?>

    <!-- Gợi ý phòng tương tự -->
    <?php if (!empty($suggestedRooms)): ?>
      <div class="suggest-box">
        <h3>Gợi Ý Phòng Tương Tự</h3>
        <div class="rooms-grid">
          <?php foreach ($suggestedRooms as $room): ?>
            <div class="room-card">
              <div class="card-img">
                <img src="<?= getRoomImageUrl($room->getType(), 400, 200) ?>"
                     alt="<?= htmlspecialchars($room->getType()) ?>">
              </div>
              <div class="card-body">
                <h3>Phòng <?= htmlspecialchars($room->getType()) ?></h3>
                <p class="card-price"><?= number_format($room->getPricePerNight(), 0, ',', '.') ?>đ / đêm</p>
                <p class="card-guests">👥 Tối đa <?= $room->getMaxGuests() ?> khách</p>
                <div class="card-actions">
                  <a href="?action=room-detail&room_id=<?= $room->getId() ?>" class="btn-detail">Xem chi tiết</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?<?= htmlspecialchars($paginationQuery) ?>&page=<?= $page-1 ?>" class="page-btn">‹ Trước</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?<?= htmlspecialchars($paginationQuery) ?>&page=<?= $i ?>"
             class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?<?= htmlspecialchars($paginationQuery) ?>&page=<?= $page+1 ?>" class="page-btn">Tiếp ›</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div><!-- /.rooms-content -->
</div><!-- /.rooms-layout -->
</section>
</main>

<style>
/* ---- Layout 2 cột ---- */
.rooms-layout {
  display: grid;
  grid-template-columns: 255px 1fr;
  gap: 28px;
  align-items: start;
  padding-top: 30px;
}

/* ---- Sidebar ---- */
.filter-sidebar {
  background: #fff;
  border: 1px solid #e0f2f1;
  border-radius: 14px;
  padding: 20px 18px;
  position: sticky;
  top: 80px;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.filter-block {
  margin-bottom: 18px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}
.filter-block:last-of-type { border-bottom: none; margin-bottom: 0; }
.filter-title { font-size:.9rem; font-weight:700; color:#009688; margin:0 0 10px; }
.filter-sidebar label:not(.am-check) {
  display:block; font-size:.8rem; color:#666; margin:6px 0 2px;
}
.filter-sidebar input[type="date"],
.filter-sidebar input[type="number"],
.filter-sidebar select {
  width:100%; padding:7px 9px; border:1px solid #ccc;
  border-radius:6px; font-size:.85rem; box-sizing:border-box;
}
.price-row { display:flex; align-items:center; gap:6px; }
.price-row input { flex:1; width:0; }
.price-row span  { color:#aaa; }
.price-presets   { display:flex; flex-wrap:wrap; gap:5px; margin-top:8px; }
.preset {
  font-size:.72rem; padding:4px 8px; background:#e0f7f4;
  border:1px solid #b2dfdb; border-radius:20px; cursor:pointer; color:#00695c;
}
.preset:hover { background:#b2dfdb; }
.amenities-list { display:flex; flex-direction:column; gap:5px; max-height:220px; overflow-y:auto; }
.am-check { display:flex; align-items:center; gap:6px; font-size:.84rem; cursor:pointer; }
.btn-apply {
  display:block; width:100%; padding:10px; background:#009688; color:#fff;
  border:none; border-radius:8px; font-size:.93rem; font-weight:600;
  cursor:pointer; margin-top:14px;
}
.btn-apply:hover { background:#00796b; }
.btn-clear {
  display:block; text-align:center; margin-top:8px;
  font-size:.82rem; color:#aaa; text-decoration:none;
}
.btn-clear:hover { color:#e53935; }

/* ---- Topbar ---- */
.list-topbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.list-topbar .result-count { font-size: .95rem; color: #444; }
.sort-select {
  padding: 7px 12px; border: 1px solid #ddd; border-radius: 8px;
  font-size: .88rem; background: #fff; cursor: pointer;
}

/* ---- Filter notice ---- */
.filter-notice {
  background:#e0f7f4; border:1px solid #b2dfdb; border-radius:8px;
  padding:10px 14px; margin-bottom:16px; font-size:.88rem;
}
.filter-notice.error { background:#fff1f2; border-color:#fecdd3; color:#be123c; }
.clear-link { margin-left:10px; color:#009688; text-decoration:none; }
.empty-state {
  padding:32px; border:1px dashed #b2dfdb; border-radius:12px;
  background:#f8fffe; text-align:center;
}

/* ---- Card nằm ngang ---- */
.rooms-list-wrap { display: flex; flex-direction: column; gap: 20px; }

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
.rch-guests {
  font-size: .85rem;
  color: #444;
  white-space: nowrap;
}

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
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
  font-size: .78rem;
  color: #888;
  margin-top: 2px;
  white-space: nowrap;
}
.rch-vat {
  background: #fef3c7; color: #92400e;
  font-size: .72rem; font-weight: 600;
  padding: 2px 7px; border-radius: 10px; margin-left: 4px;
}
.btn-book-h {
  display: block; text-align: center;
  background: #10b981; color: #fff;
  border-radius: 10px; padding: 11px 0;
  font-weight: 700; font-size: .93rem;
  text-decoration: none;
  transition: background .2s;
}
.btn-book-h:hover { background: #059669; }
.btn-detail-h {
  display: block; text-align: center;
  border: 1.5px solid #d1d5db; color: #374151;
  border-radius: 10px; padding: 10px 0;
  font-weight: 600; font-size: .9rem;
  text-decoration: none;
  transition: border-color .2s;
}
.btn-detail-h:hover { border-color: #10b981; color: #10b981; }

/* ---- Gợi ý phòng ---- */
.suggest-box { margin-top: 32px; }
.suggest-box h3 { margin-bottom: 12px; }
.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px,1fr));
  gap: 20px;
}
.card-amenities { display:flex; flex-wrap:wrap; gap:4px; margin:6px 0; }
.am-tag {
  font-size:.7rem; background:#e0f7f4; color:#00796b;
  padding:2px 7px; border-radius:12px;
}

/* ---- Phân trang ---- */
.pagination { display:flex; justify-content:center; gap:8px; margin:28px 0; }
.page-btn {
  padding:8px 14px; border:1px solid #ccc; border-radius:6px;
  text-decoration:none; color:#333;
}
.page-btn.active { background:#009688; color:#fff; border-color:#009688; }
.page-btn:hover:not(.active) { background:#f0f0f0; }

/* ---- Responsive ---- */
@media (max-width: 768px) {
  .rooms-layout { grid-template-columns: 1fr; }
  .filter-sidebar { position: static; }
}
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

<script>
// Nút gợi ý giá nhanh
document.querySelectorAll('.preset').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelector('[name="price_min"]').value = btn.dataset.min > 0 ? btn.dataset.min : '';
    document.querySelector('[name="price_max"]').value = btn.dataset.max > 0 ? btn.dataset.max : '';
  });
});

// Validate ngày trước khi submit form
document.querySelector('form').addEventListener('submit', function(e) {
    const checkIn  = document.querySelector('[name="checkin"]').value;
    const checkOut = document.querySelector('[name="checkout"]').value;

    if (checkIn && checkOut) {
        if (new Date(checkOut) <= new Date(checkIn)) {
            e.preventDefault();
            alert('⚠️ Ngày trả phòng phải sau ngày nhận phòng!');
            return;
        }
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (new Date(checkIn) < today) {
            e.preventDefault();
            alert('⚠️ Ngày nhận phòng không được là ngày trong quá khứ!');
            return;
        }
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layout/footer.php'; ?>