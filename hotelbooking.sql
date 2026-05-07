-- Tạo database
CREATE DATABASE IF NOT EXISTS hotel_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_booking;

-- =============================================
-- Bảng rooms
-- =============================================
CREATE TABLE rooms (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    room_number      VARCHAR(20)    NOT NULL UNIQUE,
    type             VARCHAR(50)    NOT NULL,
    price_per_night  DECIMAL(10,2)  NOT NULL,
    max_guests       INT            NOT NULL,
    max_adults       INT            NOT NULL DEFAULT 2,
    max_children     INT            NOT NULL DEFAULT 0,
    amenities        TEXT,
    is_active        TINYINT(1)     NOT NULL DEFAULT 1 
);

-- =============================================
-- Bảng bookings
-- =============================================
CREATE TABLE bookings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    room_id      INT            NOT NULL,
    fullname     VARCHAR(100)   NOT NULL,
    phone        VARCHAR(15)    NOT NULL,
    email        VARCHAR(100)   NOT NULL,
    check_in     DATE           NOT NULL,
    check_out    DATE           NOT NULL,
    guests       INT            NOT NULL,
    total_price  DECIMAL(12,2)  NOT NULL DEFAULT 0,
    status       VARCHAR(20)    NOT NULL DEFAULT 'pending',
    created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- =============================================
-- Dữ liệu mẫu (THÊM amenities + max_adults + max_children)
-- max_adults: số người lớn tối đa, max_children: số trẻ em tối đa
-- =============================================
INSERT INTO rooms (room_number, type, price_per_night, max_guests, max_adults, max_children, amenities, is_active) VALUES
('101', 'Standard',           500000,  2, 2, 0, '["Wi-Fi","Điều hòa","TV"]', 1),
('102', 'Standard Twin',      550000,  3, 2, 1, '["Wi-Fi","Điều hòa","2 giường"]', 1),
('201', 'Deluxe',             800000,  3, 2, 1, '["Wi-Fi","Mini bar","Ban công"]', 1),
('202', 'Deluxe Sea View',    950000,  3, 2, 1, '["Wi-Fi","Ban công","Bồn tắm"]', 1),
('203', 'Deluxe',             800000,  3, 2, 1, '["Wi-Fi","Mini bar"]', 0),
('301', 'Suite',             1500000,  5, 3, 2, '["Wi-Fi","Bồn tắm","Hồ bơi"]', 1),
('302', 'Suite Family',      1600000,  6, 3, 3, '["Wi-Fi","Bồn tắm","Hồ bơi"]', 1),
('401', 'Presidential Suite',3000000,  7, 4, 3, '["Wi-Fi","Minibar","Butler 24/7"]', 1),
('103', 'Economy',            400000,  2, 2, 0, '["Wi-Fi","TV"]', 1),
('104', 'Economy Twin',       420000,  3, 2, 1, '["Wi-Fi","2 giường","Điều hòa"]', 1),
('501', 'Business',          1200000,  3, 3, 0, '["Wi-Fi","Bàn làm việc"]', 1),
('502', 'Business Deluxe',   1300000,  4, 3, 1, '["Wi-Fi","Mini bar"]', 1),
('601', 'Luxury',            2000000,  5, 3, 2, '["Wi-Fi","Điều hòa","Ban công"]', 1),
('602', 'Luxury Sea View',   2200000,  5, 3, 2, '["Wi-Fi","View biển"]', 1),
('701', 'Penthouse',         5000000,  8, 4, 4, '["Wi-Fi","Hồ bơi","Phòng khách riêng"]', 1),
('105', 'Single Room',        350000,  1, 1, 0, '["Wi-Fi","Điều hòa","TV"]', 1),
('106', 'Double Room',        600000,  3, 2, 1, '["Wi-Fi","Điều hòa","TV"]', 1),
('107', 'Triple Room',        700000,  4, 2, 2, '["Wi-Fi","Điều hòa"]', 1),
('108', 'Quad Room',          900000,  5, 3, 2, '["Wi-Fi","Bồn tắm"]', 1),
('303', 'Family Room',       1100000,  6, 3, 3, '["Wi-Fi","Điều hòa","Bếp nhỏ"]', 1);