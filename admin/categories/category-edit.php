<?php

/*
 * Sửa danh mục theo ID trên URL: tải dữ liệu, kiểm tra POST, cập nhật bằng PDO.
 * Controller cung cấp các danh mục cha có thể chọn; SQL cập nhật nằm trong file này.
 * Form ưu tiên dữ liệu POST để giữ nội dung đã nhập khi kiểm tra không thành công.
 * Giao diện dùng shell chung và CSS categories/category-edit.css.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();

$controller = new ProductCategoryController($pdo);
// ================================
// KIỂM TRA ĐĂNG NHẬP
// ================================

if (!isset($_SESSION['admin_user'])) {

    header("Location: login.php");
    exit;
}


// ================================
// LẤY ID DANH MỤC
// ================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    die("ID danh mục không hợp lệ.");

}


// ================================
// LẤY THÔNG TIN DANH MỤC
// ================================

$sql = "
    SELECT *
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

    die("Không tìm thấy danh mục.");

}

// Có thể chọn danh mục ở bất kỳ cấp nào, ngoại trừ chính nó và các cấp con.
$parentCategories = $controller->getParentCategories($id);


// ================================
// XỬ LÝ FORM
// ================================

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? "");
    $slug = trim($_POST['slug'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $image = trim($_POST['image'] ?? "");
    $status = $_POST['status'] ?? "active";
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $parentId = !empty($_POST['parent_id'])
        ? (int) $_POST['parent_id']
        : null;

    // Kiểm tra lại ID cha phía máy chủ, không chỉ dựa vào các option trong HTML.
    $validParentIds = array_map(
        static fn (array $parent): int => (int) $parent['id'],
        $parentCategories
    );


    // ============================
    // KIỂM TRA
    // ============================

    if ($name === "") {

        $error = "Vui lòng nhập tên danh mục.";

    } elseif ($slug === "") {

        $error = "Vui lòng nhập slug.";

    } elseif (
        $parentId !== null
        && !in_array($parentId, $validParentIds, true)
    ) {

        $error = "Danh mục cha không hợp lệ.";

    } else {


        // ========================
        // KIỂM TRA SLUG TRÙNG
        // ========================

        // Loại bản ghi đang sửa khỏi kiểm tra trùng slug.
        $checkSql = "
            SELECT id
            FROM product_categories
            WHERE slug = :slug
            AND id != :id
            LIMIT 1
        ";

        $checkStmt = $pdo->prepare($checkSql);

        $checkStmt->execute([
            ':slug' => $slug,
            ':id'   => $id
        ]);

        $existing = $checkStmt->fetch();


        if ($existing) {

            $error = "Slug này đã tồn tại.";

        } else {


            // ====================
            // UPDATE
            // ====================

            $updateSql = "
                UPDATE product_categories
                SET
                    parent_id = :parent_id,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    image = :image,
                    status = :status,
                    sort_order = :sort_order
                WHERE id = :id
            ";

            $updateStmt = $pdo->prepare($updateSql);

            // Mô tả/ảnh rỗng được lưu thành null; các giá trị đi qua tham số SQL.
            $updateStmt->execute([
                ':parent_id'   => $parentId,
                ':name'        => $name,
                ':slug'        => $slug,
                ':description' => $description !== ""
                    ? $description
                    : null,
                ':image'       => $image !== ""
                    ? $image
                    : null,
                ':status'      => $status,
                ':sort_order'  => $sort_order,
                ':id'          => $id
            ]);


            // ====================
            // QUAY LẠI DANH SÁCH
            // ====================

            header("Location: categories.php?updated=1");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sửa danh mục | NHAT TRAN</title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/categories/category-edit.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">


<?php require __DIR__ . '/../includes/shell-start.php'; ?>


<main class="container">


    <div class="page-header">

        <h1>
            Sửa danh mục
        </h1>


        <a
            href="categories.php"
            class="btn btn-back"
        >
            <?= adminIcon('back') ?> Quay lại
        </a>

    </div>


    <div class="form-box">


        <?php if ($error): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">
            <?php echo adminCsrfField(); ?>

            <!-- DANH MỤC CHA -->

            <div class="form-group">

                <label for="parent_id">Danh mục cha</label>

                <select id="parent_id" name="parent_id">

                    <option value="">— Danh mục gốc —</option>

                    <?php foreach ($parentCategories as $parentCategory): ?>

                        <option
                            value="<?= (int) $parentCategory['id'] ?>"
                            <?= (int) (
                                $_POST['parent_id'] ?? $category['parent_id'] ?? 0
                            ) === (int) $parentCategory['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= str_repeat(
                                '— ',
                                (int) ($parentCategory['depth'] ?? 0)
                            ) ?><?= htmlspecialchars($parentCategory['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- TÊN -->

            <div class="form-group">

                <label for="name">

                    Tên danh mục

                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars(
                        $_POST['name']
                        ?? $category['name']
                    ) ?>"
                    required
                >

            </div>


            <!-- SLUG -->

            <div class="form-group">

                <label for="slug">

                    Slug

                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    id="slug"
                    name="slug"
                    value="<?= htmlspecialchars(
                        $_POST['slug']
                        ?? $category['slug']
                    ) ?>"
                    required
                >

            </div>


            <!-- MÔ TẢ -->

            <div class="form-group">

                <label for="description">

                    Mô tả

                </label>


                <textarea
                    id="description"
                    name="description"
                ><?= htmlspecialchars(
                    $_POST['description']
                    ?? $category['description']
                    ?? ''
                ) ?></textarea>

            </div>


            <!-- IMAGE -->

            <div class="form-group">

                <label for="image">

                    Hình ảnh

                </label>


                <input
                    type="text"
                    id="image"
                    name="image"
                    value="<?= htmlspecialchars(
                        $_POST['image']
                        ?? $category['image']
                        ?? ''
                    ) ?>"
                    placeholder="Ví dụ: uploads/categories/plc.jpg"
                >

            </div>


            <!-- STATUS -->

            <div class="form-group">

                <label for="status">

                    Trạng thái

                </label>


                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="active"
                        <?= (
                            ($_POST['status']
                            ?? $category['status'])
                            === 'active'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Hoạt động
                    </option>


                    <option
                        value="inactive"
                        <?= (
                            ($_POST['status']
                            ?? $category['status'])
                            === 'inactive'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Không hoạt động
                    </option>

                </select>

            </div>


            <!-- SORT -->

            <div class="form-group">

                <label for="sort_order">

                    Thứ tự

                </label>


                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="<?= htmlspecialchars(
                        $_POST['sort_order']
                        ?? $category['sort_order']
                        ?? 0
                    ) ?>"
                >

            </div>


            <button
                type="submit"
                class="btn btn-save"
            >

                LƯU THAY ĐỔI

            </button>


        </form>

    </div>

</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
