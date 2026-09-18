<?php
/*
 * Endpoint xóa bài viết: kiểm tra ID và bản ghi rồi gọi PostController::delete().
 * Kết quả được chuyển về trang danh sách bằng thông báo trên URL.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/PostController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
requireAdminPost();


$controller = new PostController($pdo);


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
        "Location: posts.php?error=" .
        urlencode("ID bài viết không hợp lệ")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| KIỂM TRA BÀI VIẾT
|--------------------------------------------------------------------------
*/

$post = $controller->find($id);

if (!$post) {

    header(
        "Location: posts.php?error=" .
        urlencode("Không tìm thấy bài viết")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| XÓA BÀI VIẾT
|--------------------------------------------------------------------------
*/

try {

    $result = $controller->delete($id);

    if ($result) {

        header(
            "Location: posts.php?message=" .
            urlencode("Đã xóa bài viết thành công")
        );

        exit;

    } else {

        header(
            "Location: posts.php?error=" .
            urlencode("Không thể xóa bài viết")
        );

        exit;
    }

} catch (Throwable $e) {

    header(
        "Location: posts.php?error=" .
        urlencode(
            "Lỗi khi xóa bài viết: " . $e->getMessage()
        )
    );

    exit;
}