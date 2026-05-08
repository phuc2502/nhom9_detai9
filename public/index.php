<?php
// =============================================
// File: public/index.php
// Entry point duy nhat cua ung dung
// =============================================

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$action = trim($_GET['action'] ?? 'home');
$do = trim($_GET['do'] ?? '');

switch ($action) {
    case 'home':
        require_once ROOT_PATH . '/app/services/BookingService.php';
        $service = new BookingService();
        $rooms = $service->getActiveRooms();
        include ROOT_PATH . '/app/views/home/index.php';
        break;

    case 'booking':
        require_once ROOT_PATH . '/app/controllers/BookingController.php';
        $ctrl = new BookingController();
        if ($do === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->showForm();
        }
        break;

    case 'booking/confirm':
        require_once ROOT_PATH . '/app/controllers/BookingController.php';
        $ctrl = new BookingController();
        $ctrl->confirm();
        break;

    case 'payment/qr':
        require_once ROOT_PATH . '/app/controllers/PaymentController.php';
        (new PaymentController())->showQR();
        break;

    case 'payment/cancel':
        require_once ROOT_PATH . '/app/controllers/PaymentController.php';
        (new PaymentController())->cancel();
        break;

    case 'payment/leave':
        // Gọi khi user rời trang QR (beacon/fetch khi unload)
        header('Content-Type: application/json');
        $bookingId = (int) ($_GET['booking_id'] ?? 0);
        if ($bookingId > 0) {
            require_once ROOT_PATH . '/app/services/BookingService.php';
            $svc = new BookingService();
            $booking = $svc->findBookingById($bookingId);
            if ($booking && $booking->getStatus() === 'pending') {
                $svc->updateBookingStatus($bookingId, 'cancelled');
            }
        }
        echo json_encode(['ok' => true]);
        exit;

    case 'payment/check':
        require_once ROOT_PATH . '/app/controllers/PaymentController.php';
        (new PaymentController())->checkStatus();
        break;

    case 'payment/webhook':
        require_once ROOT_PATH . '/app/controllers/PaymentController.php';
        (new PaymentController())->webhook();
        break;

    case 'payment/success':
        require_once ROOT_PATH . '/app/controllers/PaymentController.php';
        (new PaymentController())->success();
        break;

    case 'rooms':
        require_once ROOT_PATH . '/app/services/BookingService.php';
        $service = new BookingService();

        // Tham số ngày & khách
        // Tham số ngày & khách
        $filterAdults = max(0, (int) ($_GET['adults'] ?? 0));
        $filterChildren = max(0, (int) ($_GET['children'] ?? 0));
        $filterGuests = $filterAdults + $filterChildren; // tổng, dùng cho thông báo
        $totalGuests = $filterGuests;
        $filterCheckIn = trim($_GET['checkin'] ?? '');
        $filterCheckOut = trim($_GET['checkout'] ?? '');

        // Tham số bộ lọc MỚI
        $filterPriceMin = max(0, (float) ($_GET['price_min'] ?? 0));
        $filterPriceMax = max(0, (float) ($_GET['price_max'] ?? 0));
        $filterType = trim($_GET['room_type'] ?? '');
        $filterAmenities = (isset($_GET['amenities']) && is_array($_GET['amenities']))
            ? array_map('trim', $_GET['amenities']) : [];
        $filterSortBy = in_array($_GET['sort'] ?? '', ['price_asc', 'price_desc', 'guests_asc', 'type_asc'])
            ? $_GET['sort'] : 'price_asc';

        $searchError = null;
        $searchNotice = null;
        $suggestedRooms = [];
        $allRooms = [];

        // Dữ liệu cho sidebar bộ lọc
        $allTypes = $service->getDistinctTypes();
        $allAmenities = $service->getAllAmenities();

        // Xử lý ngày
        $checkInDate = null;
        $checkOutDate = null;
        $datePattern = '/^\d{4}-\d{2}-\d{2}$/';

        if ($filterCheckIn !== '' || $filterCheckOut !== '') {
            if (!preg_match($datePattern, $filterCheckIn) || !preg_match($datePattern, $filterCheckOut)) {
                $searchError = 'Ngày nhận hoặc ngày trả không đúng định dạng.';
            } else {
                try {
                    $checkInDate = new DateTime($filterCheckIn);
                    $checkOutDate = new DateTime($filterCheckOut);
                } catch (\Throwable $e) {
                    $searchError = 'Ngày không hợp lệ.';
                }
            }
        }

        // Gọi filterRooms thống nhất
        if ($searchError === null) {
            try {
                $allRooms = $service->filterRooms(
                    $filterPriceMin,
                    $filterPriceMax,
                    $filterType,
                    $filterAmenities,
                    $filterSortBy,
                    $checkInDate,
                    $checkOutDate,
                    $totalGuests,
                    $filterAdults,
                    $filterChildren
                );

                // Tạo thông báo kết quả
                $parts = [];
                if ($checkInDate && $checkOutDate)
                    $parts[] = 'từ ' . $checkInDate->format('d/m/Y') . ' đến ' . $checkOutDate->format('d/m/Y');
                if ($filterAdults > 0)
                    $parts[] = "{$filterAdults} Người lớn";
                if ($filterChildren > 0)
                    $parts[] = "{$filterChildren} Trẻ em";
                if ($filterType !== '')
                    $parts[] = "loại \"{$filterType}\"";
                if ($filterPriceMin > 0 || $filterPriceMax > 0) {
                    $mn = $filterPriceMin > 0 ? number_format($filterPriceMin, 0, ',', '.') . 'đ' : '0';
                    $mx = $filterPriceMax > 0 ? number_format($filterPriceMax, 0, ',', '.') . 'đ' : '∞';
                    $parts[] = "giá {$mn}–{$mx}";
                }
                if (!empty($filterAmenities))
                    $parts[] = 'tiện nghi: ' . implode(', ', $filterAmenities);

                if (!empty($parts))
                    $searchNotice = 'Bộ lọc: ' . implode(' | ', $parts);

                if (empty($allRooms)) {
                    // Khi có ngày tìm kiếm: loại trừ phòng đang bị đặt trùng lịch khỏi gợi ý
                    // → tránh gợi ý phòng mà user không đặt được.
                    // Khi không có ngày: $excludeIds rỗng → gợi ý phòng active bất kỳ.
                    $excludeIds = [];
                    if ($checkInDate && $checkOutDate) {
                        $availableIds = array_map(
                            fn($r) => $r->getId(),
                            $service->searchAvailableRooms($checkInDate, $checkOutDate, 0)
                        );
                        $allActiveIds = array_map(
                            fn($r) => $r->getId(),
                            $service->getActiveRooms()
                        );
                        // Phòng bị đặt = active nhưng không còn trống trong khoảng ngày đó
                        $excludeIds = array_values(array_diff($allActiveIds, $availableIds));
                    }
                    $suggestedRooms = $service->getSuggestedRooms($totalGuests, $excludeIds, 3);
                }

            } catch (\Throwable $e) {
                $searchError = 'Không thể tải danh sách phòng. Vui lòng thử lại.';
            }
        }

        // Phân trang
        $perPage = 6;
        $total = count($allRooms);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));
        $rooms = array_slice($allRooms, ($page - 1) * $perPage, $perPage);

        // Lưu ý: amenities[] là mảng, http_build_query tự tạo dạng amenities%5B0%5D=...
        // PHP đọc lại đúng thành $_GET['amenities'] là array → hoạt động bình thường.
        $paginationQuery = http_build_query(array_filter([
            'action' => 'rooms',
            'checkin' => $filterCheckIn,
            'checkout' => $filterCheckOut,
            'adults' => $filterAdults ?: null,
            'children' => $filterChildren ?: null,
            'price_min' => $filterPriceMin ?: null,
            'price_max' => $filterPriceMax ?: null,
            'room_type' => $filterType,
            'amenities' => !empty($filterAmenities) ? $filterAmenities : null,
            'sort' => $filterSortBy !== 'price_asc' ? $filterSortBy : null,
        ], fn($v) => $v !== '' && $v !== null));

        include ROOT_PATH . '/app/views/room/rooms.php';
        break;

    case 'room-detail':
        require_once ROOT_PATH . '/app/services/BookingService.php';
        $service = new BookingService();
        $roomId = (int) ($_GET['room_id'] ?? 0);
        if ($roomId <= 0) {
            header('Location: ?action=rooms');
            exit;
        }
        try {
            $room = $service->findRoomById($roomId);
        } catch (\RuntimeException $e) {
            header('Location: ?action=rooms');
            exit;
        }
        include ROOT_PATH . '/app/views/room/room-detail.php';
        break;

    case 'amenities':
        include ROOT_PATH . '/app/views/room/tiennghi.php';
        break;

    case 'contact':
        // Xử lý form liên hệ khi user nhấn "Gửi liên hệ" (POST)
        $contactSuccess = false;
        $contactError = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once ROOT_PATH . '/app/services/MailService.php';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($name !== '' && $email !== '' && $message !== '') {
                try {
                    MailService::sendContactForm($name, $email, $message);
                    $contactSuccess = true;
                } catch (\Throwable $e) {
                    $contactError = 'Không thể gửi liên hệ lúc này. Vui lòng thử lại sau.';
                }
            } else {
                $contactError = 'Vui lòng điền đầy đủ thông tin.';
            }
        }
        include ROOT_PATH . '/app/views/pages/lienhe.php';
        break;

    case 'about':
        include ROOT_PATH . '/app/views/pages/gioithieu.php';
        break;



    default:
        http_response_code(404);
        echo "<h2 style='font-family:sans-serif;text-align:center;margin-top:80px'>404 - Trang không tồn tại</h2>";
        echo "<p style='text-align:center'><a href='./'>← Về trang chủ</a></p>";
        break;
}