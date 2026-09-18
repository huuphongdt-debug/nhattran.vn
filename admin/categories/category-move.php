<?php

/*
 * Endpoint đổi vị trí danh mục; nhận GET id và direction (up/down).
 * Controller xử lý di chuyển; file này kiểm tra đầu vào và chuyển hướng sau thao tác.
 * Không xuất HTML để header() có thể gửi người dùng về danh sách.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
requireAdminPost();



/*
|--------------------------------------------------------------------------
| KIỂM TRA ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_user'])) {

    header("Location: ../login.php");

    exit;
}


$id = (int) ($_POST['id'] ?? 0);

$direction = $_POST['direction'] ?? '';


/*
|--------------------------------------------------------------------------
| GIỮ LẠI BỘ LỌC
|--------------------------------------------------------------------------
*/

// Mang theo bộ lọc và số trang để quay lại danh sách sau khi controller xử lý.
$keyword = trim($_POST['keyword'] ?? '');

$type = $_POST['type'] ?? 'all';

$status = $_POST['status'] ?? 'all';

$page = max(
    1,
    (int) ($_POST['page'] ?? 1)
);


/*
|--------------------------------------------------------------------------
| KIỂM TRA DỮ LIỆU
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    header(
        "Location: categories.php?error="
        . urlencode('ID danh mục không hợp lệ.')
    );

    exit;
}


if (!in_array($direction, ['up', 'down'], true)) {

    header(
        "Location: categories.php?error="
        . urlencode('Hướng di chuyển không hợp lệ.')
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CONTROLLER
|--------------------------------------------------------------------------
*/

$controller = new ProductCategoryController($pdo);


/*
|--------------------------------------------------------------------------
| DI CHUYỂN
|--------------------------------------------------------------------------
*/

// Kết quả gồm success và message; nội dung message dùng cho cả thành công lẫn lỗi.
$result = $controller->move(
    $id,
    $direction
);


/*
|--------------------------------------------------------------------------
| CHUYỂN VỀ TRANG DANH MỤC
|--------------------------------------------------------------------------
*/

// http_build_query mã hóa thông báo và bộ lọc thành query string cho URL chuyển hướng.
if ($result['success']) {

    $params = [
        'message' => $result['message'],
        'keyword' => $keyword,
        'type'    => $type,
        'status'  => $status,
        'page'    => $page
    ];

    header(
        "Location: categories.php?"
        . http_build_query($params)
    );

} else {

    $params = [
        'error'   => $result['message'],
        'keyword' => $keyword,
        'type'    => $type,
        'status'  => $status,
        'page'    => $page
    ];

    header(
        "Location: categories.php?"
        . http_build_query($params)
    );
}

exit;
