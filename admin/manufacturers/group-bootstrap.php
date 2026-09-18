<?php
/*
 * Khởi tạo phân nhóm hãng; cần $pdo từ database.php trước khi include.
 * Kiểm tra quyền quản lý và token groups_csrf cho POST.
 * $manufacturerGroups là danh sách nhóm; $selectedGroups lấy từ POST hoặc hãng đang sửa.
 * Các ID nhóm được đối chiếu với dữ liệu hiện có trước khi xử lý biểu mẫu.
 */

require_once __DIR__ . '/../includes/layout.php';
requireManager();
require_once __DIR__ . '/../../app/Models/ManufacturerGroup.php';
$groupModel = new ManufacturerGroup($pdo);
$manufacturerGroups = $groupModel->all();
$_SESSION['manufacturer_groups_csrf'] ??= bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !is_string($_POST['groups_csrf'] ?? null)
        || !hash_equals($_SESSION['manufacturer_groups_csrf'], $_POST['groups_csrf'])
    ) {
        http_response_code(403);
        exit('Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.');
    }
}
$selectedGroups = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? array_map('intval', (array) ($_POST['groups'] ?? []))
    : array_map(
        'intval',
        array_column($groupModel->memberships()[(int) ($_GET['id'] ?? 0)] ?? [], 'id')
    );
if (array_diff($selectedGroups, array_map('intval', array_column($manufacturerGroups, 'id')))) {
    http_response_code(400);
    exit('Danh mục hãng không còn tồn tại. Vui lòng tải lại trang.');
}
