<?php
/*
 * Thêm dịch vụ: đọc POST, chuẩn hóa slug, kiểm tra trùng rồi gọi ServiceController.
 * Các biến biểu mẫu giữ dữ liệu để hiển thị lại khi có lỗi; thành công chuyển về danh sách.
 * Giao diện dùng shell chung và CSS riêng của dịch vụ.
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
| CONTROLLER
|--------------------------------------------------------------------------
*/

$controller = new ServiceController($pdo);


/*
|--------------------------------------------------------------------------
| BIẾN
|--------------------------------------------------------------------------
*/

$error = '';

$name = '';
$slug = '';
$icon = '';
$shortDescription = '';
$content = '';
$image = '';
$sortOrder = 0;
$status = 'active';


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


            if ($existingService) {

                $error =
                    'Slug này đã tồn tại. Vui lòng chọn slug khác.';

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
                | TẠO DỊCH VỤ
                |--------------------------------------------------------------------------
                */

                try {

                    $created =
                        $controller->create(
                            $data
                        );


                    if ($created) {

                        header(
                            "Location: services.php?message="
                            . urlencode(
                                'Thêm dịch vụ thành công.'
                            )
                        );

                        exit;

                    } else {

                        $error =
                            'Không thể thêm dịch vụ.';

                    }

                } catch (PDOException $e) {

                    $error =
                        'Có lỗi xảy ra khi lưu dữ liệu.';
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
        Thêm dịch vụ | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/services/services-create.css">

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


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            Thêm dịch vụ
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
                        placeholder="Ví dụ: Lập trình PLC"
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
                        placeholder="lap-trinh-plc"
                    >


                    <div class="form-help">

                        Để trống nếu muốn hệ thống tự tạo slug.

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
                        placeholder="⚙️"
                    >


                    <div class="form-help">

                        Có thể dùng emoji, ví dụ:
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
                    placeholder="Nhập mô tả ngắn về dịch vụ..."
                ><?= htmlspecialchars($shortDescription) ?></textarea>


                <div class="form-help">

                    Nội dung này sẽ được sử dụng để giới thiệu
                    nhanh dịch vụ trên website.

                </div>

            </div>


            <!-- =================================================
                 NỘI DUNG CHI TIẾT
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
                    placeholder="Nhập nội dung chi tiết về dịch vụ..."
                ><?= htmlspecialchars($content) ?></textarea>


                <div class="form-help">

                    Phần này sẽ dùng cho trang chi tiết dịch vụ
                    sau này.

                </div>

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

                    Tạm thời nhập đường dẫn hình ảnh.
                    Phần upload hình ảnh sẽ hoàn thiện sau.

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
                    <?= adminIcon('plus') ?> Lưu dịch vụ
                </button>

            </div>


        </form>

    </div>


</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
