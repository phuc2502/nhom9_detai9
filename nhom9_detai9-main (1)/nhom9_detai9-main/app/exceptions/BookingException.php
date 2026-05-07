<?php
// =============================================
// File: app/exceptions/BookingException.php
// Mục đích: Định nghĩa tất cả Exception riêng cho hệ thống đặt phòng
//
// Tại sao tạo Exception riêng thay vì dùng Exception mặc định?
// → Khi catch, có thể phân biệt từng loại lỗi để hiển thị thông báo khác nhau
// → catch (RoomNotAvailableException $e) khác với catch (InvalidDateException $e)
// =============================================


/**
 * Exception GỐC cho toàn bộ hệ thống đặt phòng.
 * Tất cả exception bên dưới đều kế thừa (extends) từ class này.
 * Lợi ích: có thể catch tất cả lỗi booking bằng 1 lệnh:
 *   catch (BookingException $e) { ... }
 */
class BookingException extends RuntimeException {}


/**
 * Phòng đã được đặt trong khoảng thời gian yêu cầu (trùng lịch).
 * Ném ra khi: user chọn ngày mà phòng đó đã có booking khác.
 */
class RoomNotAvailableException extends BookingException {}


/**
 * Ngày check-in hoặc check-out không hợp lệ.
 * Ném ra khi: ngày quá khứ, check-out trước check-in, đặt quá xa...
 */
class InvalidDateException extends BookingException {}


/**
 * Phòng đang bảo trì hoặc tạm đóng (is_active = 0 trong database).
 * Ném ra khi: admin đặt phòng vào trạng thái bảo trì mà user vẫn cố đặt.
 */
class RoomInactiveException extends BookingException {}


/**
 * Số khách vượt quá sức chứa tối đa của phòng (max_guests).
 * Ném ra khi: user nhập số khách > max_guests của phòng đã chọn.
 */
class GuestLimitException extends BookingException {}


/**
 * Thiếu thông tin bắt buộc khi tạo booking.
 * Ném ra khi: tên/email/phone để trống, hoặc email sai định dạng.
 */
class MissingBookingInfoException extends BookingException {}
