<?php
/*
 * Cấu hình menu dùng bởi sidebar.php, theo thứ tự hiển thị.
 * Mỗi phần tử: [khóa module, nhãn, đường dẫn tương đối trong admin, mã icon].
 * Khóa module cần khớp $adminSection trong layout.php; mã icon xử lý bởi adminIcon().
 */

return [
    ['dashboard', 'Tổng quan', 'dashboard.php', '▦'],
    ['products', 'Sản phẩm', 'products/products.php', '□'],
    ['reviews', 'Đánh giá', 'reviews/reviews.php', 'star'],
    ['categories', 'Danh mục', 'categories/categories.php', '▤'],
    ['manufacturers', 'Hãng sản xuất', 'manufacturers/manufacturers.php', '◇'],
    ['services', 'Dịch vụ', 'services/services.php', '⚒'],
    ['projects', 'Dự án', 'projects/projects.php', '▧'],
    ['posts', 'Bài viết', 'posts/posts.php', '≡'],
    ['banners', 'Banner', 'banners/banners.php', '▣'],
    ['settings', 'Cài đặt', 'settings/settings.php', 'tools'],
    ['account', 'Đổi mật khẩu', 'account/password.php', 'check'],
];
