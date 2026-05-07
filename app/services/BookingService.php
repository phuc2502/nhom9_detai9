<?php
// =============================================
// File: app/services/BookingService.php
// Tầng: Service (Business Logic)
// Mục đích: Chứa toàn bộ logic nghiệp vụ đặt phòng
//
// Tại sao tách Service riêng thay vì viết trong Controller?
// → Controller chỉ được phép "điều phối" (nhận input → gọi service → trả view)
// → Service có thể tái dùng: web, API, CLI dùng chung 1 service
// → Dễ viết test: test service độc lập, không cần HTTP request
// =============================================

require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/app/models/Room.php';
require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/builders/BookingBuilder.php';
require_once ROOT_PATH . '/app/exceptions/BookingException.php';

class BookingService
{
    // $db lưu kết nối PDO dùng chung trong toàn class
    // Khởi tạo 1 lần trong constructor, dùng lại ở mọi method
    private PDO $db;

    public function __construct()
    {
        // Database::getConnection() là Singleton → chỉ tạo 1 kết nối duy nhất
        $this->db = Database::getConnection();
    }

    // ==================================================
    // NHÓM 1: CÁC HÀM KIỂM TRA (VALIDATION)
    // ==================================================

    /**
     * Kiểm tra phòng có đang hoạt động không (is_active = 1).
     * Gọi TRƯỚC KHI cho phép đặt phòng.
     *
     * @throws RoomInactiveException nếu phòng đang bảo trì
     */
    public function checkRoomActive(Room $room): void
    {
        if (!$room->isActive()) {
            throw new RoomInactiveException(
                "Phòng {$room->getRoomNumber()} ({$room->getType()}) " .
                "hiện đang bảo trì hoặc tạm đóng. Vui lòng chọn phòng khác."
            );
        }
    }

    /**
     * Validate ngày check-in và check-out.
     *
     * Các điều kiện kiểm tra:
     * 1. Check-in không phải ngày hôm qua trở về trước
     * 2. Check-out phải sau check-in ít nhất 1 đêm
     * 3. Không đặt trước hơn 365 ngày
     *
     * @throws InvalidDateException nếu ngày không hợp lệ
     */
    public function validateDates(DateTime $checkIn, DateTime $checkOut): void
    {
        $today = new DateTime('today'); // 00:00:00 hôm nay

        // Điều kiện 1: check-in không được là ngày đã qua
        if ($checkIn < $today) {
            throw new InvalidDateException(
                "Ngày check-in {$checkIn->format('d/m/Y')} đã qua. " .
                "Vui lòng chọn từ hôm nay trở đi."
            );
        }

        // Điều kiện 2: check-out phải SAU check-in ít nhất 1 đêm
        // Dùng <= thay vì < để chặn cả trường hợp checkout cùng ngày checkin
        // (checkout == checkin → 0 đêm → tổng tiền = 0đ, không hợp lệ)
        if ($checkOut <= $checkIn) {
            throw new InvalidDateException(
                "Ngày trả phòng ({$checkOut->format('d/m/Y')}) phải sau ngày nhận phòng " .
                "({$checkIn->format('d/m/Y')}) ít nhất 1 đêm."
            );
        }

    

        // Điều kiện 3: không đặt quá 365 ngày tới
        // clone: tạo bản sao DateTime để không làm thay đổi object gốc
        // modify(): thay đổi giá trị DateTime theo chuỗi
        $maxFuture = (clone $today)->modify('+365 days');
        if ($checkIn > $maxFuture) {
            throw new InvalidDateException(
                "Không thể đặt phòng trước hơn 365 ngày. Liên hệ lễ tân để biết thêm."
            );
        }
    }

    /**
     * Kiểm tra số khách có vượt sức chứa phòng không.
     *
     * @throws GuestLimitException nếu quá số người tối đa
     */
    public function validateGuestCount(Room $room, int $guestCount): void
    {
        if ($guestCount <= 0) {
            throw new GuestLimitException("Số khách phải lớn hơn 0");
        }

        if ($guestCount > $room->getMaxGuests()) {
            throw new GuestLimitException(
                "Phòng {$room->getRoomNumber()} chỉ chứa tối đa " .
                "{$room->getMaxGuests()} khách. " .
                "Bạn yêu cầu $guestCount khách."
            );
        }
    }

    /**
     * Tự động hủy tất cả booking PENDING đã quá 15 phút.
     * Gọi mỗi khi kiểm tra phòng trống để phòng được giải phóng đúng lúc.
     */
    public function cancelExpiredPendingBookings(): int
    {
        $stmt = $this->db->prepare("
            UPDATE bookings
               SET status = 'cancelled'
             WHERE status = 'pending'
               AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Kiểm tra phòng có bị đặt trùng lịch không (QUAN TRỌNG NHẤT).
     *
     * Logic overlap detection:
     * Booking B (mới) trùng với booking A (cũ) khi:
     *   B.checkIn < A.checkOut  VÀ  B.checkOut > A.checkIn
     * → Hai điều kiện phải thỏa đồng thời mới là trùng.
     *
     * Truy vấn SQL thực hiện điều này hiệu quả hơn loop PHP.
     *
     * @throws RoomNotAvailableException nếu phòng đã được đặt
     */
    public function checkDateConflict(Room $room, DateTime $checkIn, DateTime $checkOut): void
    {
        // Trước khi check, tự động hủy pending đã quá 15 phút → giải phóng phòng đúng lúc
        $this->cancelExpiredPendingBookings();

        // SQL: tìm booking nào của cùng phòng này mà chưa bị hủy và trùng ngày
        $sql = "
            SELECT id, check_in, check_out
            FROM bookings
            WHERE room_id = :room_id
              AND status != 'cancelled'
              AND check_in  < :check_out
              AND check_out > :check_in
            LIMIT 1
        ";

        // prepare(): chuẩn bị câu lệnh, ngăn SQL Injection
        $stmt = $this->db->prepare($sql);

        // execute() với mảng tham số → PDO thay :tên bằng giá trị an toàn
        $stmt->execute([
            ':room_id'   => $room->getId(),
            ':check_in'  => $checkIn->format('Y-m-d'),   // format sang string cho SQL
            ':check_out' => $checkOut->format('Y-m-d'),
        ]);

        // fetch(): lấy 1 row kết quả. Nếu có row → phòng bị trùng lịch
        $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflict) {
            // Định dạng ngày từ DB để hiển thị thân thiện
            $from = (new DateTime($conflict['check_in']))->format('d/m/Y');
            $to   = (new DateTime($conflict['check_out']))->format('d/m/Y');

            throw new RoomNotAvailableException(
                "Phòng {$room->getRoomNumber()} đã được đặt từ $from đến $to. " .
                "Vui lòng chọn ngày khác hoặc phòng khác."
            );
        }
    }

    /**
     * Kiểm tra tổng hợp: gộp 4 bước check vào 1 method.
     * Controller gọi method này thay vì gọi từng check riêng lẻ.
     */
    public function checkAvailability(Room $room, DateTime $checkIn, DateTime $checkOut, int $guestCount): bool
    {
        $this->checkRoomActive($room);                    // Check 1: phòng hoạt động?
        $this->validateDates($checkIn, $checkOut);         // Check 2: ngày hợp lệ?
        $this->validateGuestCount($room, $guestCount);     // Check 3: số khách hợp lệ?
        $this->checkDateConflict($room, $checkIn, $checkOut); // Check 4: trùng lịch?

        return true; // Nếu qua hết → phòng có thể đặt
    }

    // ==================================================
    // NHÓM 2: CÁC HÀM THAO TÁC CHÍNH
    // ==================================================

    /**
     * Tìm phòng theo ID từ database.
     * Dùng Room::fromDB() để tạo object Room từ row.
     *
     * @throws RuntimeException nếu không tìm thấy
     */
    public function findRoomById(int $roomId): Room
    {
        // Câu lệnh có tham số → dùng prepare() để tránh SQL Injection
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $roomId]);

        // PDO::FETCH_ASSOC: trả về mảng kết hợp ['column_name' => value]
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \RuntimeException("Không tìm thấy phòng có ID = $roomId");
        }

        // Room::fromDB() là static factory — xem Room.php để hiểu
        return Room::fromDB($row);
    }

    /**
     * Lấy danh sách tất cả phòng đang hoạt động (is_active = 1).
     * Dùng để hiển thị trang chọn phòng.
     *
     * @return Room[] Mảng các object Room
     */
    public function getActiveRooms(): array
    {
        // Không có tham số thay đổi → có thể dùng query() thay vì prepare()
        $stmt = $this->db->query("SELECT * FROM rooms WHERE is_active = 1 ORDER BY type, price_per_night");

        $rooms = [];
        // fetchAll(): lấy tất cả row một lúc vào mảng
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rooms[] = Room::fromDB($row); // Tạo Room object từ mỗi row
        }

        return $rooms;
    }

    /**
     * Tạo booking mới — đây là method TRUNG TÂM của hệ thống.
     *
     * Flow:
     * 1. Build booking từ builder (validate dữ liệu đầu vào)
     * 2. Kiểm tra tất cả điều kiện nghiệp vụ
     * 3. Lưu vào database
     * 4. Trả về booking với ID thật từ DB
     *
     * @throws BookingException nếu có bất kỳ lỗi nghiệp vụ nào
     */
    
    public function createBooking(BookingBuilder $builder): Booking
    {
        // Bước 1: Build — BookingBuilder validate dữ liệu đầu vào
        $booking = $builder->build();

        // Bước 2: Kiểm tra nghiệp vụ (phòng active? ngày hợp lệ? trùng lịch?)
        $this->checkAvailability(
            $booking->getRoom(),
            $booking->getCheckIn(),
            $booking->getCheckOut(),
            $booking->getGuests()
        );

        // Bước 3: Lưu vào database
        $sql = "
            INSERT INTO bookings
                (room_id, fullname, phone, email, check_in, check_out, guests, total_price, status)
            VALUES
                (:room_id, :fullname, :phone, :email, :check_in, :check_out, :guests, :total_price, :status)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':room_id'     => $booking->getRoom()->getId(),
            ':fullname'    => $booking->getFullname(),
            ':phone'       => $booking->getPhone(),
            ':email'       => $booking->getEmail(),
            ':check_in'    => $booking->getCheckIn()->format('Y-m-d'),
            ':check_out'   => $booking->getCheckOut()->format('Y-m-d'),
            ':guests'      => $booking->getGuests(),
            ':total_price' => $booking->getTotalPrice(),
            ':status'      => Booking::STATUS_PENDING,
        ]);

        // lastInsertId(): lấy ID vừa được tạo bởi AUTO_INCREMENT
        $newId = (int) $this->db->lastInsertId();

        // Bước 4: Tạo lại Booking với ID thật từ DB và trả về
        return new Booking(
            id:         $newId,
            room:       $booking->getRoom(),
            fullname:   $booking->getFullname(),
            phone:      $booking->getPhone(),
            email:      $booking->getEmail(),
            checkIn:    $booking->getCheckIn(),
            checkOut:   $booking->getCheckOut(),
            guests:     $booking->getGuests(),
            totalPrice: $booking->getTotalPrice(),
            status:     Booking::STATUS_PENDING
        );
    }

    
    /**
     * Lấy booking theo ID.
     * Dùng để hiển thị trang xác nhận sau khi đặt.
     */
    public function findBookingById(int $id): ?Booking
    {
        // JOIN rooms để lấy thông tin phòng cùng lúc, tránh 2 query riêng
        $sql = "
            SELECT b.*, r.room_number, r.type, r.price_per_night, r.max_guests, r.is_active
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null; // Trả về null nếu không tìm thấy

        // Tạo Room từ dữ liệu trong row (do đã JOIN)
        $room = Room::fromDB($row);

        // Tạo Booking từ row và room vừa tạo
        return Booking::fromDB($row, $room);
    }

    /**
     * Lấy danh sách booking của 1 email (lịch sử đặt phòng).
     *
     * @return Booking[]
     */
    public function getBookingsByEmail(string $email): array
    {
        $sql = "
            SELECT b.*, r.room_number, r.type, r.price_per_night, r.max_guests, r.is_active
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.email = :email
            ORDER BY b.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => strtolower($email)]);

        $bookings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $room       = Room::fromDB($row);
            $bookings[] = Booking::fromDB($row, $room);
        }

        return $bookings;
    }

  
    public function isRoomAvailable(int $roomId, string $checkIn, string $checkOut): bool
{
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = ?
            AND status != 'cancelled'
            AND check_in  < ?
            AND check_out > ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    return (int) $stmt->fetchColumn() === 0;
}
    /**
     * Cập nhật status booking (dùng cho webhook SePay).
     * Dùng AND status = 'pending' để đảm bảo atomic:
     * chỉ request đầu tiên update được → tránh gửi email trùng.
     */
    public function updateBookingStatus(int $bookingId, string $status): bool
    {
        $valid = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $valid, true)) return false;

        // Nếu chuyển sang confirmed: chỉ update khi đang pending
        // → ngăn polling và webhook cùng gửi email
        if ($status === 'confirmed') {
            $stmt = $this->db->prepare(
                "UPDATE bookings SET status = :status WHERE id = :id AND status = 'pending'"
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE bookings SET status = :status WHERE id = :id"
            );
        }

        $stmt->execute([':status' => $status, ':id' => $bookingId]);
        return $stmt->rowCount() > 0;
    }
    /**
 * Tìm phòng còn trống theo ngày và số khách.
 * Gọi từ index.php case 'rooms' khi user tìm kiếm có ngày.
 *
 * @throws InvalidDateException nếu ngày không hợp lệ
 * @return Room[]
 */
public function searchAvailableRooms(DateTime $checkIn, DateTime $checkOut, int $guests = 0): array
{
    // Validate ngày — dùng lại hàm có sẵn
    $this->validateDates($checkIn, $checkOut);

    $sql = "
        SELECT * FROM rooms
        WHERE is_active = 1
          AND id NOT IN (
              SELECT room_id FROM bookings
              WHERE status != 'cancelled'
                AND check_in  < :check_out
                AND check_out > :check_in
          )
    ";

    // Chỉ thêm điều kiện số khách nếu có truyền vào
    if ($guests > 0) {
        $sql .= " AND max_guests >= :guests ";
    }

    $sql .= " ORDER BY price_per_night ASC ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':check_in',  $checkIn->format('Y-m-d'),  PDO::PARAM_STR);
    $stmt->bindValue(':check_out', $checkOut->format('Y-m-d'), PDO::PARAM_STR);

    if ($guests > 0) {
        $stmt->bindValue(':guests', $guests, PDO::PARAM_INT);
    }

    $stmt->execute();

    $rooms = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rooms[] = Room::fromDB($row);
    }
    return $rooms;
}

/**
 * Gợi ý phòng tương tự khi không có phòng trống đúng yêu cầu.
 * Ưu tiên phòng gần số khách nhất, sau đó giá thấp hơn.
 *
 * @param int   $guests      Số khách cần chứa
 * @param array $excludeIds  Danh sách room_id cần bỏ qua
 * @param int   $limit       Số phòng gợi ý tối đa
 * @return Room[]
 */
public function getSuggestedRooms(int $guests, array $excludeIds = [], int $limit = 3): array
{
    $excludeSql = '';
    if (!empty($excludeIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $excludeSql   = "AND id NOT IN ($placeholders)";
    }

    $sql = "
        SELECT * FROM rooms
        WHERE is_active = 1
        $excludeSql
        ORDER BY
            CASE WHEN ? > 0 THEN ABS(max_guests - ?) ELSE 0 END ASC,
            price_per_night ASC
        LIMIT ?
    ";

    // Params: excludeIds (nếu có) + guests×2 + limit
    $params = array_merge($excludeIds, [$guests, $guests, $limit]);

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    $rooms = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rooms[] = Room::fromDB($row);
    }
    return $rooms;
}
// =========================================================
    // BỘ LỌC PHÒNG — thêm mới
    // =========================================================

    /**
     * Lọc phòng theo giá, loại, tiện nghi, sắp xếp, ngày, số khách.
     */
    public function filterRooms(
        float     $priceMin  = 0,
        float     $priceMax  = 0,
        string    $type      = '',
        array     $amenities = [],
        string    $sortBy    = 'price_asc',
        ?DateTime $checkIn   = null,
        ?DateTime $checkOut  = null,
        int       $guests    = 0
    ): array {
        $conditions = ['is_active = 1'];
        $params     = [];

        if ($priceMin > 0) {
            $conditions[] = 'price_per_night >= :price_min';
            $params[':price_min'] = $priceMin;
        }
        if ($priceMax > 0) {
            $conditions[] = 'price_per_night <= :price_max';
            $params[':price_max'] = $priceMax;
        }
        if ($type !== '') {
            $conditions[] = 'type = :type';
            $params[':type'] = $type;
        }
        if ($guests > 0) {
            $conditions[] = 'max_guests >= :guests';
            $params[':guests'] = $guests;
        }
        if ($checkIn && $checkOut) {
    // Gọi validateDates để kiểm tra ngày hợp lệ
    // (check_out phải sau check_in, không được đặt ngày quá khứ, v.v.)
        $this->validateDates($checkIn, $checkOut);

        $conditions[] = "id NOT IN (
        SELECT room_id FROM bookings
        WHERE status != 'cancelled'
          AND check_in  < :check_out
          AND check_out > :check_in
        )";
        $params[':check_in']  = $checkIn->format('Y-m-d');
        $params[':check_out'] = $checkOut->format('Y-m-d');
}

        $orderMap = [
            'price_asc'  => 'price_per_night ASC',
            'price_desc' => 'price_per_night DESC',
            'guests_asc' => 'max_guests ASC',
            'type_asc'   => 'type ASC',
        ];
        $order = $orderMap[$sortBy] ?? 'price_per_night ASC';

        $sql  = 'SELECT * FROM rooms WHERE ' . implode(' AND ', $conditions);
        $sql .= " ORDER BY $order";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rooms = array_map([Room::class, 'fromDB'], $stmt->fetchAll(PDO::FETCH_ASSOC));

        // Lọc tiện nghi ở PHP (vì lưu dạng JSON trong DB)
        if (!empty($amenities)) {
            $rooms = array_values(array_filter($rooms, function (Room $room) use ($amenities) {
                foreach ($amenities as $required) {
                    if (!in_array($required, $room->getAmenities(), true)) return false;
                }
                return true;
            }));
        }

        return $rooms;
    }

    /**
     * Lấy danh sách loại phòng duy nhất để render dropdown.
     */
    public function getDistinctTypes(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT type FROM rooms WHERE is_active = 1 ORDER BY type ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Lấy tất cả tiện nghi duy nhất từ tất cả phòng.
     */
    public function getAllAmenities(): array
{
    // Danh sách tiện nghi cố định — chỉ hiển thị đúng 10 loại này
    return [
        'Wifi',
        'Điều hòa',
        'TV',
        'Minibar',
        'Ban công/View biển',
        'Bồn tắm',
        'Bàn làm việc',
        'Phòng khách riêng',
        'Hồ bơi',
        'Butler 24/7',
    ];
}
}
