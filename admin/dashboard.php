<?php
/*
 * Trang tổng quan quản trị: nạp quyền truy cập và dữ liệu trước khi xuất HTML.
 * dashboard/data.php chuẩn bị số liệu; các partial summary, recent-products,
 * actions và status chỉ hiển thị dữ liệu trong khung shell dùng chung.
 * CSS dashboard.css được nạp trước layout.css để giữ thứ tự áp dụng giao diện.
 */
// Ngày/giờ trong tiêu đề được tính tại lúc PHP tạo trang, theo múi giờ Việt Nam.
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once __DIR__ . '/../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/../config/database.php';
// Nạp số liệu trước các partial; $dashboardError quyết định thông báo lỗi và dấu — ở thống kê.
require __DIR__ . '/dashboard/data.php';
?>
<!doctype html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tổng quan | NHAT TRAN</title>
        <link rel="stylesheet" href="assets/css/dashboard.css?v=2">
        <link rel="stylesheet" href="assets/css/layout.css">
    </head>
    <body class="admin-shell">
        <?php require __DIR__ . '/includes/shell-start.php'; ?>
        <main class="dash-main">
    <?php
    $weekdays = [
        1 => 'Thứ Hai',
        2 => 'Thứ Ba',
        3 => 'Thứ Tư',
        4 => 'Thứ Năm',
        5 => 'Thứ Sáu',
        6 => 'Thứ Bảy',
        7 => 'Chủ Nhật'
    ]; ?>
            <div class="dash-heading">
                <div>
                    <p class="dash-eyebrow">TRUNG TÂM QUẢN TRỊ</p>
                    <h1>Tổng quan website</h1>
                    <p>Xin chào, <strong><?= adminEscape($adminName) ?></strong>. Cùng cập nhật nội dung hôm nay.</p>
                </div><time datetime="<?= date('c') ?>"><?= $weekdays[(int) date('N')] ?>, <?= date('d/m/Y H:i') ?></time>
            </div>
            <?php if ($dashboardError): ?><p class="dash-alert" role="alert">Chưa tải được số liệu. Vui lòng tải lại trang hoặc kiểm tra kết nối dữ liệu.</p><?php endif; ?>
            <?php require __DIR__ . '/dashboard/summary.php'; ?>
            <div class="dash-columns">
                <div class="dash-stack">
                    <?php require __DIR__ . '/dashboard/recent-products.php'; ?>
                    <?php require __DIR__ . '/dashboard/actions.php'; ?>
                </div>
                <div class="dash-stack">
                    <?php require __DIR__ . '/dashboard/status.php'; ?>
                </div>
            </div>
            <p class="dash-footer">NHAT TRAN · Số liệu nội dung hiện tại từ hệ thống quản trị</p>
        </main>
        <?php require __DIR__ . '/includes/shell-end.php'; ?>
    </body>
</html>
