<?php

/*
 * Form thêm danh mục sản phẩm; ProductCategoryController xử lý kiểm tra và tạo dữ liệu.
 * Danh mục cha để trống biểu thị danh mục gốc; depth dùng để thụt cấp trong danh sách chọn.
 * Giao diện dùng shell chung và CSS categories/category-create.css.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();


if (!isset($_SESSION['admin_user'])) {
    header("Location: ../login.php");
    exit;
}

$controller = new ProductCategoryController($pdo);

/*
|--------------------------------------------------------------------------
| LẤY DANH MỤC CHA
|--------------------------------------------------------------------------
|
| Lấy toàn bộ cây danh mục để có thể tạo nhiều cấp.
|
*/

$categories =
    $controller->getParentCategories();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Chuyển dữ liệu biểu mẫu đến controller; lấy success/message để điều hướng hoặc báo lỗi.

    $result = $controller->create($_POST);

    if ($result['success']) {

        // Sau khi lưu, quay về GET để tải lại trang không gửi lại yêu cầu thêm mới.
        header("Location: categories.php");
        exit;

    } else {

        $error = $result['message'];
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Thêm danh mục | NHAT TRAN</title>

    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/categories/category-create.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>

<body class="admin-shell">

<?php require __DIR__ . '/../includes/shell-start.php'; ?>

<div class="container">

    <div class="header">

        <h1>Thêm danh mục sản phẩm</h1>

        <a
            href="categories.php"
            class="btn btn-back"
        >
            <?= adminIcon('back') ?> Quay lại
        </a>

    </div>


    <div class="box">

        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">
            <?php echo adminCsrfField(); ?>

            <!-- DANH MỤC CHA -->

            <div class="form-group">

                <label>
                    Danh mục cha
                </label>

                <select name="parent_id">

                    <option value="">
                        — Danh mục gốc —
                    </option>

                    <?php foreach ($categories as $category): ?>

                    <option value="<?= (int) $category['id'] ?>">
                        <?= str_repeat(
                            '— ',
                            (int) ($category['depth'] ?? 0)
                        ) ?><?= htmlspecialchars($category['name']) ?>
                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- TÊN DANH MỤC -->

            <div class="form-group">

                <label>
                    Tên danh mục
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Ví dụ: PLC Mitsubishi"
                    required
                >

            </div>

            <!-- SLUG -->

            <div class="form-group">

                <label>
                    Slug
                </label>

                <input
                    type="text"
                    name="slug"
                    placeholder="Để trống hệ thống tự tạo"
                >

            </div>

            <!-- TRẠNG THÁI -->

            <div class="form-group">

                <label>
                    Trạng thái
                </label>

                <select name="status">

                    <option value="active">
                        Hoạt động
                    </option>

                    <option value="inactive">
                        Tạm ẩn
                    </option>

                </select>

            </div>

            <!-- THỨ TỰ -->

            <div class="form-group">

                <label>
                    Thứ tự
                </label>

                <input
                    type="number"
                    name="sort_order"
                    value="0"
                >

            </div>

            <!-- BUTTON -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn-save"
                >
                    <?= adminIcon('plus') ?> Thêm danh mục
                </button>

                <a
                    href="categories.php"
                    class="btn btn-back"
                >
                    Hủy
                </a>

            </div>

        </form>

    </div>

</div>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
