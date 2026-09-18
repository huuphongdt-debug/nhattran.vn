<?php
/*
 * Sửa bài viết theo ID: tìm bản ghi qua controller rồi xử lý dữ liệu POST.
 * Slug được chuẩn hóa và kiểm tra trước khi cập nhật; dữ liệu gốc dùng điền biểu mẫu.
 * Lưu thành công chuyển hướng; khi lỗi hiển thị thông báo cùng form.
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
        "Location: posts.php?error="
        . urlencode("ID bài viết không hợp lệ.")
    );

    exit;
}


$controller = new PostController($pdo);


/*
|--------------------------------------------------------------------------
| LẤY BÀI VIẾT
|--------------------------------------------------------------------------
*/

$post = $controller->find($id);

if (!$post) {

    header(
        "Location: posts.php?error="
        . urlencode("Không tìm thấy bài viết.")
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DỮ LIỆU BAN ĐẦU
|--------------------------------------------------------------------------
*/

$title =
    $post['title'] ?? '';

$slug =
    $post['slug'] ?? '';

$excerpt =
    $post['excerpt'] ?? '';

$content =
    $post['content'] ?? '';

$image =
    $post['image'] ?? '';

$status =
    $post['status'] ?? 'draft';

$sort_order =
    (int) ($post['sort_order'] ?? 0);

$published_at = '';

if (!empty($post['published_at'])) {

    $published_at =
        date(
            'Y-m-d\TH:i',
            strtotime(
                $post['published_at']
            )
        );
}

$error = '';


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
        (int) (
            $_POST['sort_order']
            ?? 0
        );


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
    | TẠO / CHUẨN HÓA SLUG
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if ($slug === '') {

            $slug =
                $controller->makeSlug(
                    $title
                );

        } else {

            $slug =
                $controller->makeSlug(
                    $slug
                );

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
            $controller->findBySlug(
                $slug
            );

        if (
            $existingPost
            && (int) $existingPost['id'] !== $id
        ) {

            $error =
                'Slug này đã được sử dụng bởi bài viết khác.';

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

        $timestamp =
            strtotime(
                $published_at
            );

        if ($timestamp !== false) {

            $publishedDate =
                date(
                    'Y-m-d H:i:s',
                    $timestamp
                );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $updated =
                $controller->update(
                    $id,
                    [

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

                    ]
                );


            if ($updated) {

                header(
                    "Location: posts.php?message="
                    . urlencode(
                        "Cập nhật bài viết thành công."
                    )
                );

                exit;

            }


            $error =
                'Không thể cập nhật bài viết.';

        } catch (PDOException $e) {

            $error =
                'Có lỗi xảy ra khi cập nhật bài viết.';

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
        Sửa bài viết | NHAT TRAN
    </title>

    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/posts/post-edit.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">

<?php require __DIR__ . '/../includes/shell-start.php'; ?>

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Sửa bài viết
        </h1>

        <p>
            Chỉnh sửa nội dung bài viết trên website NHAT TRAN.
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
                    Tiêu đề *
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
                >

                <div class="hint">
                    Slug sẽ được chuẩn hóa tự động khi lưu.
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
                ><?= htmlspecialchars(
                    $content,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>

            </div>


            <!-- ẢNH -->

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
                >

                <div class="hint">
                    Hiện tại sử dụng đường dẫn ảnh.
                    Hệ thống Media sẽ được hoàn thiện sau.
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
                        value="<?= $sort_order ?>"
                        min="0"
                    >

                </div>


            </div>


            <!-- NGÀY XUẤT BẢN -->

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
                    Lưu thay đổi
                </button>

            </div>


        </form>

    </div>


</div>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
