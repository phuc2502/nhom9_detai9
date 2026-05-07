<?php
// Hàm trả về URL ảnh Pexels cố định theo loại phòng
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
