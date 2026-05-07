<?php
// =============================================
// File: app/models/Room.php
// Tầng: Model
// THAY ĐỔI so với file gốc:
// + Thêm $roomNumber, $isActive
// + Thêm Room::fromDB() để tạo từ row database
// =============================================

require_once ROOT_PATH . '/app/exceptions/BookingException.php';

class Room
{
    private int    $id;
    private string $roomNumber;
    private string $type;
    private float  $pricePerNight;
    private int    $maxGuests;
    private bool   $isActive;
    private array  $amenities;



    public function __construct(
        int    $id,
        string $roomNumber,
        string $type,
        float  $pricePerNight,
        int    $maxGuests,
        bool   $isActive = true,
        array  $amenities = []
    ) {
        $this->id            = $id;
        $this->roomNumber    = $roomNumber;
        $this->type          = $type;
        $this->pricePerNight = $pricePerNight;
        $this->maxGuests     = $maxGuests;
        $this->isActive      = $isActive;
        $this->amenities     = $amenities;

    }

    public function getId(): int              { return $this->id; }
    public function getRoomNumber(): string   { return $this->roomNumber; }
    public function getType(): string         { return $this->type; }
    public function getPricePerNight(): float { return $this->pricePerNight; }
    public function getMaxGuests(): int       { return $this->maxGuests; }
    public function getIsActive(): bool       { return $this->isActive; }
    public function getAmenities(): array
{
    return $this->amenities;
}


    /**
     * Kiểm tra phòng đang hoạt động không (is_active = 1 trong DB).
     * BookingService gọi trước khi cho đặt phòng.
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Static factory: tạo Room từ 1 row PDO fetch().
     * Cách dùng: $room = Room::fromDB($row);
     * static → gọi qua Room::fromDB() không cần new trước
     */
    public static function fromDB(array $row): self
{
    $amenities = [];

    if (!empty($row['amenities'])) {
        $decoded = json_decode($row['amenities'], true);

        if (is_array($decoded)) {
            $amenities = $decoded;
        }
    }

    return new self(
        (int)   $row['id'],
                $row['room_number'],
                $row['type'],
        (float) $row['price_per_night'],
        (int)   $row['max_guests'],
        (bool)  $row['is_active'],
                $amenities
    );
}

}
