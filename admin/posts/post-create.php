<?php
/*
 * Thêm bài viết: nhận POST, chuẩn hóa slug và kiểm tra trùng trước khi gọi controller.
 * Các biến biểu mẫu giữ nội dung nhập để xuất lại khi có lỗi; mặc định là bản nháp.
 * Lưu thành công chuyển về danh sách; dữ liệu hiển thị trong form cần được escape.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/PostController.php";

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();


if (!isset($_SESSION['admin_user'])) {
    header("Location: ../login.php");
    exit;
}

$controller = new PostController($pdo);

$error = '';

$title         = '';
$slug          = '';
$excerpt       = '';
$content       = '';
$image         = '';
$status        = 'draft';
$published_at  = '';
$sort_order    = 0;


/*
|--------------------------------------------------------------------------
| XỬ LÝ FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    $slug =
        trim($_POST['slug'] ?? '');

    $excerpt =
        trim($_POST['excerpt'] ?? '');

    $content =
        trim($_POST['content'] ?? '');

    $image =
        trim($_POST['image'] ?? '');

    $status =
        $_POST['status'] ?? 'draft';

    $published_at =
        trim($_POST['published_at'] ?? '');

    $sort_order =
        (int) ($_POST['sort_order'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA TIÊU ĐỀ
    |--------------------------------------------------------------------------
    */

    if ($title === '') {

        $error =
            'Vui lòng nhập tiêu đề bài viết.';

    }


    /*
    |--------------------------------------------------------------------------
    | TẠO SLUG TỰ ĐỘNG
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if ($slug === '') {

            $slug =
                $controller->makeSlug($title);

        } else {

            $slug =
                $controller->makeSlug($slug);

        }

        if ($slug === '') {

            $error =
                'Không thể tạo slug từ tiêu đề.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA SLUG TRÙNG
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $existingPost =
            $controller->findBySlug($slug);

        if ($existingPost) {

            $error =
                'Slug này đã tồn tại. Vui lòng chọn slug khác.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | XỬ LÝ NGÀY XUẤT BẢN
    |--------------------------------------------------------------------------
    */

    $publishedDate = null;

    if (
        $status === 'published'
        && $published_at !== ''
    ) {

        $publishedDate =
            date(
                'Y-m-d H:i:s',
                strtotime($published_at)
            );

    }


    /*
    |--------------------------------------------------------------------------
    | LƯU
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $created =
                $controller->create([

                    'title' =>
                        $title,

                    'slug' =>
                        $slug,

                    'excerpt' =>
                        $excerpt !== ''
                            ? $excerpt
                            : null,

                    'content' =>
                        $content !== ''
                            ? $content
                            : null,

                    'image' =>
                        $image !== ''
                            ? $image
                            : null,

                    'status' =>
                        $status,

                    'published_at' =>
                        $publishedDate,

                    'sort_order' =>
                        $sort_order

                ]);


            if ($created) {

                header(
                    "Location: posts.php?message="
                    . urlencode(
                        "Thêm bài viết thành công."
                    )
                );

                exit;

            }


            $error =
                'Không thể thêm bài viết.';

        } catch (PDOException $e) {

            $error =
                'Có lỗi xảy ra khi lưu bài viết.';

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
        Thêm bài viết | NHAT TRAN
    </title>

    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/posts/post-create.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">

<?php require __DIR__ . '/../includes/shell-start.php'; ?>

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Thêm bài viết
        </h1>

        <p>
            Tạo bài viết mới cho website NHAT TRAN.
        </p>

    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <div class="form-card">

        <form
            method="POST"
            action=""
        >
            <?php echo adminCsrfField(); ?>


            <!-- TIÊU ĐỀ -->

            <div class="form-group">

                <label for="title">

                    Tiêu đề

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars(
                        $title,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Nhập tiêu đề bài viết..."
                    required
                >

            </div>


            <!-- SLUG -->

            <div class="form-group">

                <label for="slug">
                    Slug
                </label>

                <input
                    type="text"
                    id="slug"
                    name="slug"
                    value="<?= htmlspecialchars(
                        $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="vi-du-bai-viet"
                >

                <div class="hint">
                    Để trống để hệ thống tự tạo slug từ tiêu đề.
                </div>

            </div>


            <!-- MÔ TẢ -->

            <div class="form-group">

                <label for="excerpt">
                    Mô tả ngắn
                </label>

                <textarea
                    id="excerpt"
                    name="excerpt"
                    placeholder="Nhập mô tả ngắn cho bài viết..."
                ><?= htmlspecialchars(
                    $excerpt,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>

            </div>


            <!-- NỘI DUNG -->

            <div class="form-group">

                <label for="content">
                    Nội dung
                </label>

                <textarea
                    id="content"
                    name="content"
                    class="content-textarea"
                    placeholder="Nhập nội dung bài viết..."
                ><?= htmlspecialchars(
                    $content,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>

                <div class="hint">
                    Hiện tại dùng trình soạn thảo văn bản cơ bản.
                    Chúng ta sẽ nâng cấp editor sau.
                </div>

            </div>


            <!-- IMAGE -->

            <div class="form-group">

                <label for="image">
                    Ảnh đại diện
                </label>

                <input
                    type="text"
                    id="image"
                    name="image"
                    value="<?= htmlspecialchars(
                        $image,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="images/posts/example.jpg"
                >

                <div class="hint">
                    Hiện tại nhập đường dẫn ảnh.
                    Phần upload Media sẽ hoàn thiện sau.
                </div>

            </div>


            <!-- GRID -->

            <div class="form-grid">


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
                            value="draft"
                            <?= $status === 'draft'
                                ? 'selected'
                                : '' ?>
                        >
                            Bản nháp
                        </option>

                        <option
                            value="published"
                            <?= $status === 'published'
                                ? 'selected'
                                : '' ?>
                        >
                            Đã xuất bản
                        </option>

                        <option
                            value="inactive"
                            <?= $status === 'inactive'
                                ? 'selected'
                                : '' ?>
                        >
                            Ngừng hiển thị
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
                        value="<?= (int) $sort_order ?>"
                        min="0"
                    >

                </div>


            </div>


            <!-- PUBLISHED DATE -->

            <div class="form-group">

                <label for="published_at">
                    Ngày xuất bản
                </label>

                <input
                    type="datetime-local"
                    id="published_at"
                    name="published_at"
                    value="<?= htmlspecialchars(
                        $published_at,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <div class="hint">
                    Có thể để trống nếu bài viết đang ở trạng thái bản nháp.
                </div>

            </div>


            <!-- ACTIONS -->

            <div class="form-actions">

                <a
                    href="posts.php"
                    class="btn btn-secondary"
                >
                    <?= adminIcon('back') ?> Quay lại
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Lưu bài viết
                </button>

            </div>


        </form>

    </div>


</div>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
