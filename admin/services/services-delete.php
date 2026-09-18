<?php
/*
 * Endpoint xóa dịch vụ: kiểm tra ID, tìm bản ghi rồi gọi ServiceController::delete().
 * Chuyển về danh sách cùng thông báo theo kết quả xử lý; không có form chỉnh sửa tại đây.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ServiceController.php";

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


/*
|--------------------------------------------------------------------------
| KIỂM TRA ID
|--------------------------------------------------------------------------
*/

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($id <= 0) {

    header(
        "Location: services.php?error="
        . urlencode("ID dịch vụ không hợp lệ.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CONTROLLER
|--------------------------------------------------------------------------
*/

$controller = new ServiceController($pdo);


/*
|--------------------------------------------------------------------------
| KIỂM TRA DỊCH VỤ
|--------------------------------------------------------------------------
*/

$service = $controller->find($id);

if (!$service) {

    header(
        "Location: services.php?error="
        . urlencode("Không tìm thấy dịch vụ.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| XÓA DỊCH VỤ
|--------------------------------------------------------------------------
*/

try {

    $deleted =
        $controller->delete($id);


    if ($deleted) {

        header(
            "Location: services.php?message="
            . urlencode(
                "Xóa dịch vụ thành công."
            )
        );

        exit;

    }


    header(
        "Location: services.php?error="
        . urlencode(
            "Không thể xóa dịch vụ."
        )
    );

    exit;


} catch (PDOException $e) {

    header(
        "Location: services.php?error="
        . urlencode(
            "Có lỗi xảy ra khi xóa dịch vụ."
        )
    );

    exit;
}