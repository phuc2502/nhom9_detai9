<?php
// =============================================
// File: app/models/Room.php
// Tầng: Model
// THAY ĐỔI:
// + Thêm $roomNumber, $isActive
// + Thêm $maxAdults, $maxChildren
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
    private int    $maxAdults;
    private int    $maxChildren;
    private bool   $isActive;
    private array  $amenities;

    public function __construct(
        int    $id,
        string $roomNumber,
        string $type,
        float  $pricePerNight,
        int    $maxGuests,
        bool   $isActive = true,
        array  $amenities = [],
        int    $maxAdults = 2,
        int    $maxChildren = 0
    ) {
        $this->id            = $id;
        $this->roomNumber    = $roomNumber;
        $this->type          = $type;
        $this->pricePerNight = $pricePerNight;
        $this->maxGuests     = $maxGuests;
        $this->isActive      = $isActive;
        $this->amenities     = $amenities;
        $this->maxAdults     = $maxAdults;
        $this->maxChildren   = $maxChildren;
    }

    public function getId(): int              { return $this->id; }
    public function getRoomNumber(): string   { return $this->roomNumber; }
    public function getType(): string         { return $this->type; }
    public function getPricePerNight(): float { return $this->pricePerNight; }
    public function getMaxGuests(): int       { return $this->maxGuests; }
    public function getMaxAdults(): int       { return $this->maxAdults; }
    public function getMaxChildren(): int     { return $this->maxChildren; }
    public function getIsActive(): bool       { return $this->isActive; }
    public function getAmenities(): array     { return $this->amenities; }

    public function isActive(): bool
    {
        return $this->isActive;
    }

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
                    $amenities,
            (int)  ($row['max_adults']   ?? 2),
            (int)  ($row['max_children'] ?? 0)
        );
    }
}
