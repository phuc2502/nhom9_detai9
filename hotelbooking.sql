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
-- Dữ liệu mẫu (THÊM amenities)
-- =============================================
INSERT INTO rooms (room_number, type, price_per_night, max_guests, amenities, is_active) VALUES
('101', 'Standard',           500000,  2, '["Wi-Fi","Máy lạnh","TV","Tủ lạnh nhỏ"]', 1),
('102', 'Standard Twin',      550000,  2, '["Wi-Fi","Máy lạnh","TV","2 giường"]', 1),
('201', 'Deluxe',             800000,  3, '["Wi-Fi","Máy lạnh","TV","Mini bar","Ban công"]', 1),
('202', 'Deluxe Sea View',    950000,  3, '["Wi-Fi","Máy lạnh","TV","Mini bar","Ban công","View biển"]', 1),
('203', 'Deluxe',             800000,  3, '["Wi-Fi","Máy lạnh","TV","Mini bar"]', 0),
('301', 'Suite',             1500000,  4, '["Wi-Fi","Máy lạnh","TV","Bồn tắm","Phòng khách","Mini bar"]', 1),
('302', 'Suite Family',      1600000,  5, '["Wi-Fi","Máy lạnh","TV","Bồn tắm","Phòng khách","Gia đình"]', 1),
('401', 'Presidential Suite',3000000,  6, '["Wi-Fi","Máy lạnh","TV","Jacuzzi","Phòng khách","Butler","Mini bar"]', 1),
('103', 'Economy',            400000,  2, '["Wi-Fi","Máy lạnh","TV"]', 1),
('104', 'Economy Twin',       420000,  2, '["Wi-Fi","Máy lạnh","TV","2 giường"]', 1),
('501', 'Business',          1200000,  3, '["Wi-Fi","Máy lạnh","TV","Bàn làm việc","Két an toàn"]', 1),
('502', 'Business Deluxe',   1300000,  3, '["Wi-Fi","Máy lạnh","TV","Bàn làm việc","Mini bar","Két an toàn"]', 1),
('601', 'Luxury',            2000000,  4, '["Wi-Fi","Máy lạnh","TV","Sàn gỗ","Ban công","Mini bar","Bồn tắm"]', 1),
('602', 'Luxury Sea View',   2200000,  4, '["Wi-Fi","Máy lạnh","TV","View biển","Ban công","Mini bar","Bồn tắm"]', 1),
('701', 'Penthouse',         5000000,  6, '["Wi-Fi","Máy lạnh","TV","Hồ bơi riêng","Sân thượng","Jacuzzi","Mini bar","Butler"]', 1),
('105', 'Single Room',        350000,  1, '["Wi-Fi","Máy lạnh","TV","Bàn làm việc"]', 1),
('106', 'Double Room',        600000,  2, '["Wi-Fi","Máy lạnh","TV","Bàn trang điểm","Tủ lạnh nhỏ"]', 1),
('107', 'Triple Room',        700000,  3, '["Wi-Fi","Máy lạnh","TV","3 giường","Tủ lạnh nhỏ"]', 1),
('108', 'Quad Room',          900000,  4, '["Wi-Fi","Máy lạnh","TV","4 giường","Tủ lạnh nhỏ"]', 1),
('303', 'Family Room',       1100000,  5, '["Wi-Fi","Máy lạnh","TV","Gia đình","Tủ lạnh nhỏ","Phòng khách"]', 1);