<?php
// =============================================
// File: app/controllers/BookingController.php
// Tầng: Controller
// Mục đích: Nhận request từ form → gọi Service → trả dữ liệu về View
//
// NGUYÊN TẮC Controller:
// ✅ Được phép: nhận $_POST/$_GET, gọi Service, include View
// ❌ Không được: viết SQL, logic nghiệp vụ, tính toán phức tạp
// → "Điều phối viên" chứ không phải "người thực hiện"
// =============================================

require_once ROOT_PATH . '/app/services/BookingService.php';
require_once ROOT_PATH . '/app/services/MailService.php';      // ← THÊM: để gửi email
require_once ROOT_PATH . '/app/builders/BookingBuilder.php';
require_once ROOT_PATH . '/app/exceptions/BookingException.php';

class BookingController
{
    // Service xử lý toàn bộ logic — Controller chỉ gọi, không tự làm
    private BookingService $service;

    public function __construct()
    {
        // Khởi tạo service 1 lần trong constructor
        // Mọi method trong class đều dùng chung $this->service
        $this->service = new BookingService();
    }

    // ==================================================
    // ACTION 1: Hiển thị form đặt phòng
    // URL: /booking hoặc /app/views/booking/booking.php
    // ==================================================

    /**
     * Hiển thị form đặt phòng.
     * Load danh sách phòng từ DB để hiển thị dropdown.
     *
     * GET /booking
     */
    public function showForm(): void
    {
        $rooms = $this->service->getActiveRooms();

        // Nếu có room_id từ URL (?action=booking&room_id=5) → preselect phòng đó
        $preselectedRoom = null;
        $roomId = (int)($_GET['room_id'] ?? 0);
        if ($roomId > 0) {
            foreach ($rooms as $room) {
                if ($room->getId() === $roomId) {
                    $preselectedRoom = $room;
                    break;
                }
            }
        }

        $this->render('booking/booking', [
            'rooms'           => $rooms,
            'preselectedRoom' => $preselectedRoom,
        ]);
    }

    // ==================================================
    // ACTION 2: Xử lý form submit → tạo booking
    // URL: POST /booking/store
    // ==================================================

    /**
     * Xử lý dữ liệu form đặt phòng khi user nhấn "Xác nhận đặt phòng".
     *
     * POST /booking/store
     * Input từ $_POST: room_id, fullname, phone, email, checkin, checkout, guests
     */
    public function store(): void
    {
        // Bước 1: Lấy và làm sạch dữ liệu từ form
        // trim(): loại khoảng trắng thừa đầu/cuối
        // (int): ép kiểu sang số nguyên để tránh injection
        $roomId   = (int)   trim($_POST['room_id']   ?? 0);
        $fullname =         trim($_POST['fullname']   ?? '');
        $phone    =         trim($_POST['phone']      ?? '');
        $email    =         trim($_POST['email']      ?? '');
        $checkIn  =         trim($_POST['checkin']    ?? '');
        $checkOut =         trim($_POST['checkout']   ?? '');
        $guests   = (int)   trim($_POST['guests']     ?? 1);
        $adults   = (int)  trim($_POST['adults']   ?? 1);   // mới
        $children = (int)  trim($_POST['children'] ?? 0);   // mới
        $note     =        trim($_POST['note']     ?? '');   // mới
        $payment  =        trim($_POST['payment']  ?? '');   // mới
        // Validate thêm tại Controller
        if ($adults < 1) {
            $this->renderBookingError('Số người lớn phải ít nhất là 1.');
            return;
        }
        if ($children < 0) {
            $this->renderBookingError('Số trẻ em không được âm.');
            return;
        }
        if (!in_array($payment, ['sepay', 'counter'], true)) {
            $this->renderBookingError('Vui lòng chọn phương thức thanh toán.');
            return;
        }

        $guests = $adults + $children; // tổng khách truyền vào builder

        // Kiểm tra tổng khách không vượt sức chứa phòng TRƯỚC KHI vào builder
        try {
            $roomForCheck = $this->service->findRoomById($roomId);
            if ($adults > $roomForCheck->getMaxAdults()) {
                $this->renderBookingError(
                    "Số người lớn ({$adults} người) vượt quá giới hạn phòng " .
                    "(tối đa {$roomForCheck->getMaxAdults()} người lớn). " .
                    "Vui lòng giảm số người lớn."
                );
                return;
            }
            if ($children > $roomForCheck->getMaxChildren()) {
                $this->renderBookingError(
                    "Số trẻ em ({$children} trẻ em) vượt quá giới hạn phòng " .
                    "(tối đa {$roomForCheck->getMaxChildren()} trẻ em). " .
                    "Vui lòng giảm số trẻ em."
                );
                return;
            }
            if ($guests > $roomForCheck->getMaxGuests()) {
                $this->renderBookingError(
                    "Tổng số khách ({$guests} người) vượt quá sức chứa phòng " .
                    "(tối đa {$roomForCheck->getMaxGuests()} khách). " .
                    "Vui lòng giảm số người lớn hoặc trẻ em."
                );
                return;
            }
        } catch (\Exception $e) {
            $this->renderBookingError('Không tìm thấy phòng. Vui lòng thử lại.');
            return;
        }

        try {
            // Bước 2: Tìm phòng theo ID được chọn
            // Service ném RuntimeException nếu không tìm thấy
            $room = $this->service->findRoomById($roomId);

            // Bước 3: Dùng BookingBuilder để xây dựng booking từng bước
            // Method chaining: mỗi set*() trả về $builder chính nó
            $builder = (new BookingBuilder())
                ->setRoom($room)
                ->setCheckIn($checkIn)    // Validate ngày trong quá khứ
                ->setCheckOut($checkOut)  // Validate check-out > check-in
                ->setGuestCount($guests)  // Validate > 0
                ->setGuestInfo($fullname, $phone, $email); // Validate tên/SĐT/email

            // Bước 4: Gọi service tạo booking
            // Service sẽ: validate nghiệp vụ + kiểm tra trùng lịch + lưu DB
            $booking = $this->service->createBooking($builder);

            // ── GỬI EMAIL XÁC NHẬN ĐẶT PHÒNG ──
            // try/catch riêng: nếu gửi email lỗi, booking VẪN thành công
            // (không để lỗi email phá hỏng trải nghiệm đặt phòng)
            try {
                MailService::sendBookingConfirmation($booking);
            } catch (\Throwable $e) {
                // Chỉ ghi log, không hiện lỗi cho khách
            }

            // Bước 5: Thành công → redirect sang trang xác nhận
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['last_booking_id'] = $booking->getId();

            // Nếu chọn VietQR → trang QR, nếu tại quầy → xác nhận luôn + gửi email
            if ($payment === 'sepay') {
                $_SESSION['payment_booking_id'] = $booking->getId();
                header('Location: ' . $this->url('payment/qr') . '&booking_id=' . $booking->getId());
            } else {
                // Tại quầy: giả định đã thanh toán → đổi status confirmed
                $updated = $this->service->updateBookingStatus($booking->getId(), 'confirmed');

                // Gửi email xác nhận thanh toán nếu update thành công
                if ($updated) {
                    try {
                        $freshBooking = $this->service->findBookingById($booking->getId());
                        if ($freshBooking) {
                            MailService::sendPaymentConfirmation($freshBooking);
                        }
                    } catch (\Throwable $e) {
                        // Ghi log, không ảnh hưởng redirect
                    }
                }

                header('Location: ' . $this->url('booking/confirm'));
            }
            exit;

        } catch (RoomNotAvailableException $e) {
            $this->renderBookingError($e->getMessage());

        } catch (RoomInactiveException $e) {
            $this->renderBookingError($e->getMessage());

        } catch (InvalidDateException $e) {
            $this->renderBookingError($e->getMessage());

        } catch (GuestLimitException $e) {
            $this->renderBookingError($e->getMessage());

        } catch (BookingException $e) {
            $this->renderBookingError($e->getMessage());

        } catch (\Exception $e) {
            $this->renderBookingError('Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    // ==================================================
    // ACTION 3: Trang xác nhận đặt phòng thành công
    // ==================================================

    /**
     * Hiển thị trang xác nhận sau khi đặt phòng thành công.
     * Lấy booking ID từ session, tìm booking từ DB, hiển thị chi tiết.
     *
     * GET /booking/confirm
     */
    public function confirm(): void
    {
         ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Lấy booking ID từ session (được lưu trong store() sau khi thành công)
        $bookingId = (int) ($_SESSION['last_booking_id'] ?? 0);

        if ($bookingId === 0) {
            // Không có session → redirect về trang đặt phòng
            header('Location: ' . $this->url('booking'));
            exit;
        }

        // Tìm booking trong DB theo ID
        $booking = $this->service->findBookingById($bookingId);

        if (!$booking) {
            header('Location: ' . $this->url('booking'));
            exit;
        }

        // Xóa session sau khi đã đọc xong (tránh reload hiện lại trang confirm cũ)
        unset($_SESSION['last_booking_id']);

        // Hiển thị trang xác nhận với dữ liệu booking
        $this->render('booking/booking-confirm', ['booking' => $booking]);
    }

    // ==================================================
    // HELPER METHODS (Phương thức hỗ trợ nội bộ)
    // ==================================================

    /**
     * Include file View và truyền dữ liệu vào.
     *
     * @param string $view  Đường dẫn view tính từ app/views/ (không có .php)
     * @param array  $data  Dữ liệu truyền vào view
     *
     * extract($data): chuyển ['booking' => $obj] thành biến $booking trong view
     * ob_start/get_clean: không bắt buộc ở đây, nhưng cho phép xử lý output nếu cần
     */
    /**
     * Render lại form booking khi có lỗi.
     * LUÔN truyền preselectedRoom nếu room_id có trong POST
     * → View luôn hiển thị card phòng đẹp thay vì dropdown.
     */
    private function renderBookingError(string $errorMsg): void
    {
        $roomId = (int)($_POST['room_id'] ?? 0);
        $preselectedRoom = null;

        // Tìm lại phòng theo room_id từ form → để view hiện card phòng, không hiện dropdown
        if ($roomId > 0) {
            try {
                $preselectedRoom = $this->service->findRoomById($roomId);
            } catch (\Exception $e) {
                // Không tìm được phòng → fallback về dropdown
            }
        }

        $this->render('booking/booking', [
            'error'           => $errorMsg,
            'rooms'           => $this->service->getActiveRooms(),
            'preselectedRoom' => $preselectedRoom,  // ← KEY FIX: luôn truyền vào
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        // extract() biến mảng associative thành các biến PHP
        // Ví dụ: ['rooms' => $rooms, 'error' => 'lỗi'] → $rooms và $error
        extract($data);

        // ROOT_PATH được define trong config.php
        $viewPath = ROOT_PATH . "/app/views/{$view}.php";

        if (!file_exists($viewPath)) {
            die("View không tồn tại: $viewPath");
        }

        include $viewPath;
    }

    /**
     * Tạo URL tương đối dựa trên base path của project.
     * Giúp link hoạt động đúng dù deploy ở subfolder hay root.
     */

private function url(string $path): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/?' . 'action=' . ltrim($path, '/');
}
}
