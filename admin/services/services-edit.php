<?php
/*
 * Sửa dịch vụ theo ID: tải bản ghi, nhận POST, kiểm tra slug và gọi controller cập nhật.
 * Biểu mẫu dùng dữ liệu đang chỉnh sửa; thông báo lỗi xuất cùng form.
 * Giữ tên các trường đồng bộ với dữ liệu ServiceController nhận.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ServiceController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();



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

$id = isset($_GET['id'])
    ? (int) $_GET['id']
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
| LẤY DỊCH VỤ
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
| BIẾN FORM
|--------------------------------------------------------------------------
*/

$error = '';

$name =
    $service['name'] ?? '';

$slug =
    $service['slug'] ?? '';

$icon =
    $service['icon'] ?? '';

$shortDescription =
    $service['short_description'] ?? '';

$content =
    $service['content'] ?? '';

$image =
    $service['image'] ?? '';

$sortOrder =
    (int) (
        $service['sort_order'] ?? 0
    );

$status =
    $service['status'] ?? 'active';


/*
|--------------------------------------------------------------------------
| XỬ LÝ FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim(
        $_POST['name'] ?? ''
    );

    $slug = trim(
        $_POST['slug'] ?? ''
    );

    $icon = trim(
        $_POST['icon'] ?? ''
    );

    $shortDescription = trim(
        $_POST['short_description'] ?? ''
    );

    $content = trim(
        $_POST['content'] ?? ''
    );

    $image = trim(
        $_POST['image'] ?? ''
    );

    $sortOrder = (int) (
        $_POST['sort_order'] ?? 0
    );

    $status =
        $_POST['status'] ?? 'active';


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA TÊN
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $error =
            'Vui lòng nhập tên dịch vụ.';

    } else {


        /*
        |--------------------------------------------------------------------------
        | TẠO SLUG
        |--------------------------------------------------------------------------
        */

        if ($slug === '') {

            $slug =
                $controller->makeSlug(
                    $name
                );

        } else {

            $slug =
                $controller->makeSlug(
                    $slug
                );
        }


        /*
        |--------------------------------------------------------------------------
        | KIỂM TRA SLUG
        |--------------------------------------------------------------------------
        */

        if ($slug === '') {

            $error =
                'Không thể tạo slug cho dịch vụ.';

        } else {


            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA SLUG TRÙNG
            |--------------------------------------------------------------------------
            */

            $existingService =
                $controller->findBySlug(
                    $slug
                );


            /*
             * Nếu slug tồn tại nhưng thuộc chính
             * dịch vụ đang sửa thì vẫn cho phép.
             */

            if (
                $existingService
                && (int) $existingService['id'] !== $id
            ) {

                $error =
                    'Slug này đã được sử dụng bởi dịch vụ khác.';

            } else {


                /*
                |--------------------------------------------------------------------------
                | DỮ LIỆU
                |--------------------------------------------------------------------------
                */

                $data = [

                    'name' =>
                        $name,

                    'slug' =>
                        $slug,

                    'short_description' =>
                        $shortDescription !== ''
                            ? $shortDescription
                            : null,

                    'content' =>
                        $content !== ''
                            ? $content
                            : null,

                    'image' =>
                        $image !== ''
                            ? $image
                            : null,

                    'icon' =>
                        $icon !== ''
                            ? $icon
                            : null,

                    'sort_order' =>
                        $sortOrder,

                    'status' =>
                        in_array(
                            $status,
                            ['active', 'inactive'],
                            true
                        )
                            ? $status
                            : 'active'
                ];


                /*
                |--------------------------------------------------------------------------
                | CẬP NHẬT
                |--------------------------------------------------------------------------
                */

                try {

                    $updated =
                        $controller->update(
                            $id,
                            $data
                        );


                    if ($updated) {

                        header(
                            "Location: services.php?message="
                            . urlencode(
                                'Cập nhật dịch vụ thành công.'
                            )
                        );

                        exit;

                    } else {

                        $error =
                            'Không thể cập nhật dịch vụ.';
                    }

                } catch (PDOException $e) {

                    $error =
                        'Có lỗi xảy ra khi cập nhật dữ liệu.';
                }
            }
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

    <title>
        Sửa dịch vụ | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/services/services-edit.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">


<!-- =====================================================
     HEADER
===================================================== -->

<?php require __DIR__ . '/../includes/shell-start.php'; ?>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="container">


    <div class="page-header">

        <h1>
            Sửa dịch vụ
        </h1>


        <a
            href="services.php"
            class="btn btn-dashboard"
        >
            <?= adminIcon('back') ?> Danh sách dịch vụ
        </a>

    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <div class="form-box">

        <form
            method="POST"
            action=""
        >
            <?php echo adminCsrfField(); ?>


            <!-- =================================================
                 TÊN + SLUG
            ================================================== -->

            <div class="form-grid">


                <div class="form-group">

                    <label for="name">

                        Tên dịch vụ

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input"
                        value="<?= htmlspecialchars($name) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="slug">

                        Slug

                    </label>


                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        class="form-input"
                        value="<?= htmlspecialchars($slug) ?>"
                    >


                    <div class="form-help">

                        Slug được dùng cho URL của dịch vụ.

                    </div>

                </div>


            </div>


            <!-- =================================================
                 ICON + THỨ TỰ
            ================================================== -->

            <div class="form-grid">


                <div class="form-group">

                    <label for="icon">

                        Icon

                    </label>


                    <input
                        type="text"
                        id="icon"
                        name="icon"
                        class="form-input"
                        value="<?= htmlspecialchars($icon) ?>"
                    >


                    <div class="form-help">

                        Ví dụ:
                        ⚙️ 🔧 <?= adminIcon('building') ?> 🤖 💻

                    </div>

                </div>


                <div class="form-group">

                    <label for="sort_order">

                        Thứ tự hiển thị

                    </label>


                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        class="form-input"
                        value="<?= $sortOrder ?>"
                        min="0"
                    >

                </div>


            </div>


            <!-- =================================================
                 MÔ TẢ NGẮN
            ================================================== -->

            <div class="form-group">

                <label for="short_description">

                    Mô tả ngắn

                </label>


                <textarea
                    id="short_description"
                    name="short_description"
                    class="form-textarea"
                    placeholder="Nhập mô tả ngắn..."
                ><?= htmlspecialchars($shortDescription) ?></textarea>

            </div>


            <!-- =================================================
                 NỘI DUNG
            ================================================== -->

            <div class="form-group">

                <label for="content">

                    Nội dung chi tiết

                </label>


                <textarea
                    id="content"
                    name="content"
                    class="form-textarea"
                    style="min-height: 220px;"
                    placeholder="Nhập nội dung chi tiết..."
                ><?= htmlspecialchars($content) ?></textarea>

            </div>


            <!-- =================================================
                 HÌNH ẢNH
            ================================================== -->

            <div class="form-group">

                <label for="image">

                    Hình ảnh

                </label>


                <input
                    type="text"
                    id="image"
                    name="image"
                    class="form-input"
                    value="<?= htmlspecialchars($image) ?>"
                    placeholder="images/services/plc.jpg"
                >


                <div class="form-help">

                    Tạm thời sử dụng đường dẫn hình ảnh.
                    Phần upload ảnh sẽ hoàn thiện sau.

                </div>

            </div>


            <!-- =================================================
                 TRẠNG THÁI
            ================================================== -->

            <div class="form-group">

                <label for="status">

                    Trạng thái

                </label>


                <select
                    id="status"
                    name="status"
                    class="form-select"
                >

                    <option
                        value="active"
                        <?= $status === 'active'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Hoạt động
                    </option>


                    <option
                        value="inactive"
                        <?= $status === 'inactive'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Tạm ẩn
                    </option>

                </select>

            </div>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">

                <a
                    href="services.php"
                    class="btn btn-dashboard"
                >
                    Hủy
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= adminIcon('check') ?> Lưu thay đổi
                </button>

            </div>


        </form>

    </div>


</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
