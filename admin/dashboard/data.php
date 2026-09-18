<?php
/*
 * Dữ liệu dùng chung cho các partial dashboard; cần $pdo từ database.php.
 * Model Dashboard trả counts, statuses, latest, draftPosts và draftProjects.
 * Khi truy vấn lỗi, giữ cấu trúc dữ liệu để trang vẫn dựng được giao diện.
 */
require_once __DIR__ . '/../../app/Models/Dashboard.php';
require_once __DIR__ . '/../../app/Models/ProductReadiness.php';
// Dùng cùng quy tắc với trang sản phẩm; lỗi kiểm tra không làm mất số liệu khác.
$readinessCount = null;
try {
    $readinessCount = count(ProductReadiness::inspect($pdo));
} catch (Throwable $error) {
    error_log('Dashboard product readiness: ' . $error->getMessage());
}
$dashboardError = false;
try {
    $overview = (new Dashboard($pdo))->overview();
} catch (Throwable $error) {
    // Chi tiết lỗi ghi vào log máy chủ; giao diện chỉ hiện thông báo chung và dấu —.
    error_log('Dashboard: ' . $error->getMessage());
    $dashboardError = true;
    $overview = [
        'counts' => [],
        'statuses' => [],
        'latest' => [],
        'draftPosts' => 0,
        'draftProjects' => 0
    ];
}
// Mỗi thẻ: [khóa thống kê, nhãn, đường dẫn trong admin, mã icon].
$dashboardCards = [
    ['products', 'Sản phẩm', 'products/products.php', '□'],
    ['projects', 'Dự án', 'projects/projects.php', '▧'],
    ['posts', 'Bài viết', 'posts/posts.php', '≡'],
    ['services', 'Dịch vụ', 'services/services.php', '⚒'],
];
// Mỗi thao tác: [nhãn, đường dẫn trong admin, mô tả, mã icon].
// Các partial chuyển đường dẫn qua adminUrl() và mã icon qua adminIcon().
$dashboardActions = [
    ['Thêm sản phẩm', 'products/product-create.php', 'Thiết bị và thông số kỹ thuật', '□'],
    ['Thêm dự án', 'projects/projects.php?new=1', 'Hồ sơ và hình ảnh triển khai', '▧'],
    ['Viết bài mới', 'posts/post-create.php', 'Tin tức và kiến thức kỹ thuật', '≡'],
    ['Quản lý banner', 'banners/banners.php', 'Cập nhật nội dung trang chủ', '▣'],
];
