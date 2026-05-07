<?php
// =============================================
// File: app/models/Booking.php
// Tầng: Model
// THAY ĐỔI so với file gốc:
// + Thêm $fullname, $phone, $email (thông tin khách)
// + Thêm $totalPrice (tổng tiền đã tính sẵn)
// + Thêm $status (pending/confirmed/cancelled)
// + Thêm $createdAt (thời điểm đặt)
// + Thêm Booking::fromDB() để tạo từ row database
// =============================================

class Booking
{
    // Hằng số trạng thái: dùng const tránh hardcode string dễ typo
    const STATUS_PENDING   = 'pending';    // Mới đặt, chờ xác nhận
    const STATUS_CONFIRMED = 'confirmed';  // Đã xác nhận (admin duyệt / thanh toán)
    const STATUS_CANCELLED = 'cancelled';  // Đã hủy

    private int      $id;
    private Room     $room;
    private string   $fullname;     // Họ tên người đặt
    private string   $phone;        // Số điện thoại
    private string   $email;        // Email
    private DateTime $checkIn;
    private DateTime $checkOut;
    private int      $guests;
    private float    $totalPrice;   // Tổng tiền đã tính (lưu vào DB để không tính lại)
    private string   $status;
    private string   $createdAt;    // Thời điểm tạo booking

    public function __construct(
        int      $id,
        Room     $room,
        string   $fullname,
        string   $phone,
        string   $email,
        DateTime $checkIn,
        DateTime $checkOut,
        int      $guests,
        float    $totalPrice,
        string   $status    = self::STATUS_PENDING,
        string   $createdAt = ''
    ) {
        $this->id         = $id;
        $this->room       = $room;
        $this->fullname   = $fullname;
        $this->phone      = $phone;
        $this->email      = $email;
        $this->checkIn    = $checkIn;
        $this->checkOut   = $checkOut;
        $this->guests     = $guests;
        $this->totalPrice = $totalPrice;
        $this->status     = $status;
        $this->createdAt  = $createdAt ?: date('Y-m-d H:i:s');
    }

    // ---- Getters ----
    public function getId(): int              { return $this->id; }
    public function getRoom(): Room           { return $this->room; }
    public function getFullname(): string     { return $this->fullname; }
    public function getPhone(): string        { return $this->phone; }
    public function getEmail(): string        { return $this->email; }
    public function getCheckIn(): DateTime    { return $this->checkIn; }
    public function getCheckOut(): DateTime   { return $this->checkOut; }
    public function getGuests(): int          { return $this->guests; }
    public function getTotalPrice(): float    { return $this->totalPrice; }
    public function getStatus(): string       { return $this->status; }
    public function getCreatedAt(): string    { return $this->createdAt; }

    /**
     * Tính số đêm ở.
     * diff() trả về DateInterval, ->days là số ngày chênh lệch tuyệt đối.
     */
    public function getNights(): int
    {
        return (int) $this->checkIn->diff($this->checkOut)->days;
    }

    /**
     * Tính tổng tiền dựa trên giá phòng × số đêm.
     * (Phiên bản đơn giản, không tính phụ phí cuối tuần)
     */
    public function calculateTotal(): float
    {
        return $this->getNights() * $this->room->getPricePerNight();
    }

    /**
     * Static factory: tạo Booking từ 1 row PDO fetch().
     * $row phải có sẵn thông tin room (join từ DB hoặc truyền object Room vào).
     */
    public static function fromDB(array $row, Room $room): self
    {
        return new self(
            (int)   $row['id'],
                    $room,
                    $row['fullname'],
                    $row['phone'],
                    $row['email'],
            new DateTime($row['check_in']),
            new DateTime($row['check_out']),
            (int)   $row['guests'],
            (float) $row['total_price'],
                    $row['status'],
                    $row['created_at']
        );
    }
}
