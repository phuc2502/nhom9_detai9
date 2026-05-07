<?php
// Dữ liệu mẫu cho các phòng
$staticRooms = [
    [
        "name" => "Phòng Deluxe",
        "price" => "1.200.000đ / đêm",
        "status" => "Còn phòng",
        "badge_class" => "green",
        "guests" => "2 người lớn, 1 trẻ em",
        "image" => "https://picsum.photos/400/200?random=31"
    ],
    [
        "name" => "Phòng Suite",
        "price" => "2.500.000đ / đêm",
        "status" => "Chưa VAT",
        "badge_class" => "orange",
        "guests" => "2 người lớn",
        "image" => "https://picsum.photos/400/200?random=32"
    ],
    [
        "name" => "Phòng Family",
        "price" => "1.800.000đ / đêm",
        "status" => "Hết phòng",
        "badge_class" => "red",
        "guests" => "4 người lớn, 2 trẻ em",
        "image" => "https://picsum.photos/400/200?random=33"
    ]
];

// Dữ liệu mẫu cho tiện nghi
$staticAmenities = [
    ["icon" => "🏊", "name" => "Bể bơi ngoài trời"],
    ["icon" => "🍽️", "name" => "Nhà hàng sang trọng"],
    ["icon" => "💪", "name" => "Phòng gym hiện đại"],
    ["icon" => "🛏️", "name" => "Phòng nghỉ tiện nghi"],
    ["icon" => "🚗", "name" => "Dịch vụ đưa đón"],
    ["icon" => "🌐", "name" => "Wi-Fi tốc độ cao"],
    ["icon" => "☕", "name" => "Quầy cafe & bar"],
    ["icon" => "🧖", "name" => "Spa & massage"],
    ["icon" => "👶", "name" => "Dịch vụ trông trẻ"],
    ["icon" => "🅿️", "name" => "Bãi đỗ xe miễn phí"]
];
// Hàm trả về URL ảnh Unsplash cố định theo loại phòng
function getRoomImageUrl(string $type, int $width = 400, int $height = 200): string {
    $photos = [
        'Standard'           => 'https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg',
        'Standard Twin'      => 'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg',
        'Deluxe'             => 'https://images.pexels.com/photos/1579253/pexels-photo-1579253.jpeg',
        'Deluxe Sea View'    => 'https://images.pexels.com/photos/2373201/pexels-photo-2373201.jpeg',
        'Suite'              => 'https://images.pexels.com/photos/262048/pexels-photo-262048.jpeg',
        'Suite Family'       => 'https://images.pexels.com/photos/1454806/pexels-photo-1454806.jpeg',
        'Presidential Suite' => 'https://images.pexels.com/photos/1743229/pexels-photo-1743229.jpeg',
        'Economy'            => 'https://images.pexels.com/photos/279746/pexels-photo-279746.jpeg',
        'Economy Twin'       => 'https://images.pexels.com/photos/237371/pexels-photo-237371.jpeg',
        'Business'           => 'https://images.pexels.com/photos/2029694/pexels-photo-2029694.jpeg',
        'Business Deluxe'    => 'https://images.pexels.com/photos/1743231/pexels-photo-1743231.jpeg',
        'Luxury'             => 'https://images.pexels.com/photos/1457842/pexels-photo-1457842.jpeg',
        'Luxury Sea View'    => 'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg',
        'Penthouse'          => 'https://images.pexels.com/photos/2869215/pexels-photo-2869215.jpeg',
        'Single Room'        => 'https://images.pexels.com/photos/271619/pexels-photo-271619.jpeg',
        'Double Room'        => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
        'Triple Room'        => 'https://images.pexels.com/photos/210265/pexels-photo-210265.jpeg',
        'Quad Room'          => 'https://images.pexels.com/photos/1648776/pexels-photo-1648776.jpeg',
        'Family Room'        => 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg',
    ];

    $url = $photos[$type] ?? 'https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg';
    return $url . "?auto=compress&cs=tinysrgb&w={$width}&h={$height}&fit=crop";
}