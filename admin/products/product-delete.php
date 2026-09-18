<?php
/*
 * Endpoint xóa sản phẩm qua POST và CSRF; kiểm tra bản ghi trước khi xử lý xóa.
 * Các truy vấn và bước xử lý liên quan nằm phía dưới; kết thúc bằng chuyển về danh sách.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . '/../../app/Helpers/UploadCleanupHelper.php';

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
requireAdminPost();



/*
|--------------------------------------------------------------------------
| KIỂM TRA ID
|--------------------------------------------------------------------------
*/

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {

    header("Location: products.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| KIỂM TRA SẢN PHẨM CÓ TỒN TẠI
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM products
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$product) {

    header(
        "Location: products.php?message="
        . urlencode("Sản phẩm không tồn tại.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| XÓA SẢN PHẨM
|--------------------------------------------------------------------------
*/

try {

    $imageQuery = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? UNION SELECT image FROM products WHERE id = ?');
    $imageQuery->execute([$id, $id]);
    $deletedImages = $imageQuery->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        DELETE FROM products
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    foreach ($deletedImages as $deletedImage) cleanupDeletedUpload($pdo, $deletedImage);


    /*
    |--------------------------------------------------------------------------
    | QUAY VỀ DANH SÁCH
    |--------------------------------------------------------------------------
    */

    header(
        "Location: products.php?message="
        . urlencode("Xóa sản phẩm thành công.")
    );

    exit;


} catch (PDOException $e) {

    die(
        "Không thể xóa sản phẩm: "
        . htmlspecialchars($e->getMessage())
    );
}
