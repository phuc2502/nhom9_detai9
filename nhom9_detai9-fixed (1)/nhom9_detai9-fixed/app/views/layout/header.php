<?php
// =============================================
// File: app/views/layout/header.php
// THAY ĐỔI so với file gốc:
// + Đường dẫn CSS và link nav dùng ROOT_PATH / base URL thật
//   thay vì hardcode /Frontend/...
// + Link CSS dùng đường dẫn tương đối từ public/
// =============================================

// Tính base URL tự động dựa trên vị trí index.php
// Ví dụ: project ở localhost/Hotel_Booking_Final/public/ → base = /Hotel_Booking_Final/public
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Tên file hiện tại để highlight menu active
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuxStay Hotel</title>
    <!-- CSS dùng đường dẫn tuyệt đối từ public/ -->
    <link rel="stylesheet" href="<?= $base ?>/css/style.css">
</head>
<body>

<header class="header">

    <a href="<?= $base ?>/?action=home" class="header-logo">
        <span>Lux</span>Stay
    </a>

    <nav>
        <ul class="header-nav">
            <li>
                <a href="<?= $base ?>/?action=home"
                   class="<?= ($currentFile === 'index.php' && ($_GET['action'] ?? '') === 'home') ? 'active' : '' ?>">
                   Trang Chủ
                </a>
            </li>
            <li>
                <a href="<?= $base ?>/?action=rooms"
                   class="<?= (($_GET['action'] ?? '') === 'rooms') ? 'active' : '' ?>">
                   Danh Sách Phòng
                </a>
            </li>
            <li>
                <a href="<?= $base ?>/?action=amenities"
                   class="<?= (($_GET['action'] ?? '') === 'amenities') ? 'active' : '' ?>">
                   Tiện Nghi
                </a>
            </li>
            <li>
                <a href="<?= $base ?>/?action=contact"
                   class="<?= (($_GET['action'] ?? '') === 'contact') ? 'active' : '' ?>">
                   Liên Hệ
                </a>
            </li>
            <li>
                <a href="<?= $base ?>/?action=about"
                   class="<?= (($_GET['action'] ?? '') === 'about') ? 'active' : '' ?>">
                   Giới Thiệu
                </a>
            </li>
            
        </ul>
    </nav>

    

</header>
