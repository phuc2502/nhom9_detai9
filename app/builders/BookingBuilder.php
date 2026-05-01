<?php
// =============================================
// File: app/builders/BookingBuilder.php
// Tầng: Builder (Pattern)
// Mục đích: Xây dựng object Booking từng bước, validate từng trường
//
// Builder Pattern giải quyết vấn đề gì?
// Nếu dùng constructor thẳng:
//   new Booking($id, $room, $name, $phone, $email, $in, $out, $guests, $price)
// → Rất dễ nhầm thứ tự tham số, khó đọc.
// Với Builder:
//   (new BookingBuilder())->setRoom($r)->setCheckIn($d)->setGuestInfo(...)->build()
// → Rõ ràng, validate từng bước, dễ mở rộng thêm field mới.
// =============================================

require_once ROOT_PATH . '/app/models/Room.php';
require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/exceptions/BookingException.php';

class BookingBuilder
{
    // Tất cả field đều nullable (?) vì chưa được set ngay lúc khởi tạo
    private ?Room   $room       = null;
    private ?string $checkIn    = null;  // Lưu dạng string 'Y-m-d', convert sang DateTime trong build()
    private ?string $checkOut   = null;
    private ?int    $guestCount = null;
    private ?string $fullname   = null;
    private ?string $phone      = null;
    private ?string $email      = null;

    // ---- Setter Methods ----
    // Mỗi setter đều return $this (chính đối tượng builder)
    // → Cho phép Method Chaining:
    //   $builder->setRoom($r)->setCheckIn('2025-08-10')->setGuestCount(2)

    public function setRoom(Room $room): self
    {
        $this->room = $room;
        return $this;   // return $this để chain tiếp
    }

    /**
     * Set ngày check-in.
     * Validate: không cho đặt ngày trong quá khứ.
     * @throws InvalidDateException
     */
    public function setCheckIn(string $checkIn): self
    {
        // Kiểm tra định dạng ngày hợp lệ trước khi tạo DateTime
        $date = DateTime::createFromFormat('Y-m-d', $checkIn);
        if (!$date) {
            throw new InvalidDateException("Ngày check-in không đúng định dạng (YYYY-MM-DD): $checkIn");
        }

        $today = new DateTime('today'); // 00:00:00 hôm nay
        if ($date < $today) {
            throw new InvalidDateException(
                "Ngày check-in không được là ngày trong quá khứ. Bạn nhập: " .
                $date->format('d/m/Y')
            );
        }

        $this->checkIn = $checkIn;
        return $this;
    }

    /**
     * Set ngày check-out.
     * Validate: phải sau check-in, không quá 30 đêm.
     * @throws InvalidDateException
     */
    public function setCheckOut(string $checkOut): self
    {
        $date = DateTime::createFromFormat('Y-m-d', $checkOut);
        if (!$date) {
            throw new InvalidDateException("Ngày check-out không đúng định dạng (YYYY-MM-DD): $checkOut");
        }

        // Nếu đã có check-in thì so sánh ngay
        if ($this->checkIn !== null) {
            $inDate  = new DateTime($this->checkIn);
            $nights  = (int) $inDate->diff($date)->days;

        // Tính ngày tối thiểu = check-in + 1 ngày
        // clone để không thay đổi $inDate gốc khi gọi modify()
        $minCheckOut = clone $inDate;
        $minCheckOut->modify('+1 day');

        if ($date < $minCheckOut) {
            throw new InvalidDateException(
                "Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 đêm. " .
                "Check-in: " . $inDate->format('d/m/Y') . " | Check-out tối thiểu: " . $minCheckOut->format('d/m/Y')
            );
        }

            // Khách sạn thường giới hạn đặt online không quá 30 đêm
            if ($nights > 30) {
                throw new InvalidDateException(
                    "Không thể đặt online quá 30 đêm liên tiếp ($nights đêm). " .
                    "Liên hệ lễ tân để đặt dài hạn."
                );
            }
        }

        $this->checkOut = $checkOut;
        return $this;
    }

    /**
     * Set số khách.
     * Validate: phải >= 1.
     */
    public function setGuestCount(int $count): self
    {
        if ($count <= 0) {
            throw new \InvalidArgumentException("Số khách phải lớn hơn 0, bạn nhập: $count");
        }
        $this->guestCount = $count;
        return $this;
    }

    /**
     * Set thông tin khách hàng: tên, điện thoại, email.
     * Validate cả 3 trường trong 1 method vì chúng liên quan nhau.
     * @throws MissingBookingInfoException
     */
    public function setGuestInfo(string $fullname, string $phone, string $email): self
    {
        // Validate tên: trim() loại bỏ khoảng trắng đầu/cuối
        if (trim($fullname) === '') {
            throw new MissingBookingInfoException("Họ tên không được để trống");
        }

        // Validate SĐT: chỉ chứa chữ số, độ dài 6–15 (chuẩn E.164 quốc tế)
        if (!preg_match('/^[0-9]{6,15}$/', $phone)) {
            throw new MissingBookingInfoException(
                "Số điện thoại không hợp lệ: '$phone'. Chỉ chứa chữ số, 6–15 ký tự."
            );
        }

        // Validate email dùng hàm built-in PHP, an toàn hơn tự viết regex
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MissingBookingInfoException("Email không hợp lệ: '$email'");
        }

        $this->fullname = trim($fullname);
        $this->phone    = $phone;
        $this->email    = strtolower($email);  // Chuẩn hóa email thành chữ thường
        return $this;
    }

    /**
     * Bước cuối: tạo object Booking sau khi đã set đủ thông tin.
     * Kiểm tra tất cả field bắt buộc trước khi tạo.
     * @throws MissingBookingInfoException nếu thiếu thông tin
     */
    public function build(): Booking
    {
        // Kiểm tra field bắt buộc
        $this->validateRequiredFields();

        // Tính tổng tiền: số đêm × giá/đêm
        $checkInDate  = new DateTime($this->checkIn);
        $checkOutDate = new DateTime($this->checkOut);
        $nights       = (int) $checkInDate->diff($checkOutDate)->days;
        $totalPrice   = $nights * $this->room->getPricePerNight();

        // Tạo và trả về Booking (id=0 vì chưa lưu DB, BookingService sẽ lấy ID thật sau insert)
        return new Booking(
            id:         0,
            room:       $this->room,
            fullname:   $this->fullname,
            phone:      $this->phone,
            email:      $this->email,
            checkIn:    $checkInDate,
            checkOut:   $checkOutDate,
            guests:     $this->guestCount,
            totalPrice: $totalPrice
        );
    }

    /**
     * Kiểm tra các field bắt buộc đã được set chưa.
     * Private vì chỉ dùng nội bộ trong build().
     */
    private function validateRequiredFields(): void
    {
        // Dùng array để kiểm tra gọn, dễ thêm field mới
        $fields = [
            'Phòng'           => $this->room,
            'Ngày check-in'   => $this->checkIn,
            'Ngày check-out'  => $this->checkOut,
            'Số khách'        => $this->guestCount,
            'Họ tên'          => $this->fullname,
            'Số điện thoại'   => $this->phone,
            'Email'           => $this->email,
        ];

        foreach ($fields as $label => $value) {
            // null = chưa set, '' = string rỗng — cả hai đều không hợp lệ
            if ($value === null || $value === '') {
                throw new MissingBookingInfoException("Thiếu thông tin bắt buộc: $label");
            }
        }
    }
}
