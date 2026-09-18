<?php
/*
 * Chuẩn bị dữ liệu danh sách: quyền truy cập, bộ lọc, tổng số và phân trang.
 * ProductController cung cấp dữ liệu; products.php và views/index.php dùng các biến này.
 * $perPage quyết định số sản phẩm mỗi trang; URL phân trang cần giữ bộ lọc hiện tại.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductController.php";

requireManager();

require_once __DIR__ . '/../../app/Models/ProductReadiness.php';
$productReadiness = ProductReadiness::inspect($pdo);


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
| CONTROLLER
|--------------------------------------------------------------------------
*/

$controller = new ProductController($pdo);


/*
|--------------------------------------------------------------------------
| BỘ LỌC
|--------------------------------------------------------------------------
*/

$keyword = trim(
    $_GET['keyword'] ?? ''
);

$statusFilter =
    $_GET['status'] ?? '';

$categoryId =
    isset($_GET['category_id'])
        ? (int) $_GET['category_id']
        : 0;


/*
|--------------------------------------------------------------------------
| PHÂN TRANG
|--------------------------------------------------------------------------
*/

$page =
    isset($_GET['page'])
        ? max(
            1,
            (int) $_GET['page']
        )
        : 1;

$perPage = 20;


/*
|--------------------------------------------------------------------------
| TỔNG SẢN PHẨM
|--------------------------------------------------------------------------
*/

$totalProducts =
    $controller->countPaginated(
        $keyword,
        $statusFilter,
        $categoryId
    );


/*
|--------------------------------------------------------------------------
| TỔNG SỐ TRANG
|--------------------------------------------------------------------------
*/

$totalPages =
    max(
        1,
        (int) ceil(
            $totalProducts / $perPage
        )
    );


/*
|--------------------------------------------------------------------------
| KIỂM TRA PAGE
|--------------------------------------------------------------------------
*/

if ($page > $totalPages) {

    $page = $totalPages;
}


/*
|--------------------------------------------------------------------------
| LẤY SẢN PHẨM
|--------------------------------------------------------------------------
*/

$products =
    $controller->index(
        $keyword,
        $statusFilter,
        $categoryId,
        $page,
        $perPage
    );


/*
|--------------------------------------------------------------------------
| THÔNG BÁO
|--------------------------------------------------------------------------
*/

$message =
    $_GET['message'] ?? '';

$error =
    $_GET['error'] ?? '';


/*
|--------------------------------------------------------------------------
| LẤY DANH MỤC
|--------------------------------------------------------------------------
|
| Nếu Controller hiện tại đã cung cấp $categories thì giữ nguyên.
| Nếu chưa có, lấy trực tiếp từ database.
|
|--------------------------------------------------------------------------
*/

$sqlCategories = "
    SELECT
        id,
        name
    FROM product_categories
    WHERE status = 'active'
    ORDER BY
        sort_order ASC,
        id ASC
";

$stmtCategories =
    $pdo->query($sqlCategories);

$categories =
    $stmtCategories->fetchAll();



