<?php
/*
 * Danh sách CSS dùng chung; thứ tự nạp quyết định quy tắc nào ghi đè.
 * $includeProductStyles bật stylesheet sản phẩm khi trang gọi yêu cầu.
 */
 /* Thứ tự CSS giữ nguyên để không đổi giao diện danh sách sản phẩm. */ ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php if ($includeProductStyles ?? false): ?><link rel="stylesheet" href="css/products.css"><?php endif; ?>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/pages.css?v=2">
<link rel="stylesheet" href="css/navigation.css">
