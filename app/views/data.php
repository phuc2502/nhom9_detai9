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
        'Deluxe'             => 'https://images.pexels.com/photos/19025564/pexels-photo-19025564.jpeg',
        'Deluxe Sea View'    => 'https://images.pexels.com/photos/14746032/pexels-photo-14746032.jpeg',
        'Suite'              => 'https://images.pexels.com/photos/18285946/pexels-photo-18285946.jpeg',
        'Suite Family'       => 'https://images.pexels.com/photos/1454806/pexels-photo-1454806.jpeg',
        'Presidential Suite' => 'https://images.pexels.com/photos/3769710/pexels-photo-3769710.jpeg',
        'Economy'            => 'https://images.pexels.com/photos/279746/pexels-photo-279746.jpeg',
        'Economy Twin'       => 'https://images.pexels.com/photos/237371/pexels-photo-237371.jpeg',
        'Business'           => 'https://images.pexels.com/photos/3201763/pexels-photo-3201763.jpeg',
        'Business Deluxe'    => 'https://images.pexels.com/photos/12663145/pexels-photo-12663145.jpeg',
        'Luxury'             => 'https://images.pexels.com/photos/1457842/pexels-photo-1457842.jpeg',
        'Luxury Sea View'    => 'https://images.pexels.com/photos/12715501/pexels-photo-12715501.jpeg',
        'Penthouse'          => 'https://images.pexels.com/photos/2869215/pexels-photo-2869215.jpeg',
        'Single Room'        => 'https://images.pexels.com/photos/5461582/pexels-photo-5461582.jpeg',
        'Double Room'        => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
        'Triple Room'        => 'https://images.pexels.com/photos/210265/pexels-photo-210265.jpeg',
        'Quad Room'          => 'https://images.pexels.com/photos/1648776/pexels-photo-1648776.jpeg',
        'Family Room'        => 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg',
    ];

    $url = $photos[$type] ?? 'https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg';
    return $url . "?auto=compress&cs=tinysrgb&w={$width}&h={$height}&fit=crop";
}