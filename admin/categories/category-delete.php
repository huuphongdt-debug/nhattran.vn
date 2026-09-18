<?php

/*
 * Endpoint xóa danh mục theo GET id; không xuất giao diện, kết thúc bằng chuyển hướng.
 * Chỉ xóa danh mục được chọn, giữ sản phẩm và các danh mục con.
 * Các mã lỗi trên URL được chuyển về categories.php để hiển thị thông báo.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
requireAdminPost();

// =====================================
// KIỂM TRA ĐĂNG NHẬP
// =====================================

if (!isset($_SESSION['admin_user'])) {

    header("Location: login.php");
    exit;
}


// =====================================
// KIỂM TRA ID
// =====================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;


if ($id <= 0) {

    header("Location: categories.php?error=invalid_id");
    exit;
}


// =====================================
// KIỂM TRA DANH MỤC CÓ TỒN TẠI KHÔNG
// =====================================

$sql = "
    SELECT id, name
    FROM product_categories
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$category = $stmt->fetch();


if (!$category) {

    header("Location: categories.php?error=not_found");
    exit;
}


// =====================================
// XÓA DANH MỤC AN TOÀN
//
// - Sản phẩm vẫn được giữ lại, chuyển về chưa phân loại.
// - Danh mục con vẫn được giữ lại, chuyển thành danh mục gốc.
// =====================================

try {

    // Ba bước gỡ liên kết và xóa phải cùng thành công; lỗi thì rollback toàn bộ.
    $pdo->beginTransaction();

    $detachProducts = $pdo->prepare(
        "UPDATE products SET category_id = NULL WHERE category_id = :id"
    );

    $detachProducts->execute([
        ':id' => $id
    ]);

    $detachChildren = $pdo->prepare(
        "UPDATE product_categories SET parent_id = NULL WHERE parent_id = :id"
    );

    $detachChildren->execute([
        ':id' => $id
    ]);

    $delete = $pdo->prepare(
        "DELETE FROM product_categories WHERE id = :id"
    );

    $delete->execute([
        ':id' => $id
    ]);

    $pdo->commit();

} catch (Throwable $exception) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: categories.php?error=delete_failed");
    exit;
}


// =====================================
// QUAY LẠI DANH SÁCH
// =====================================

header(
    "Location: categories.php?message="
    . urlencode(
        'Đã xóa danh mục. Sản phẩm liên quan được giữ lại và chuyển về chưa phân loại.'
    )
);
exit;
