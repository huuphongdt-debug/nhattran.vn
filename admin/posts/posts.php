<?php
/*
 * Danh sách bài viết lấy qua PostController::index().
 * Giao diện có bảng desktop và thẻ mobile; khi sửa trường hoặc thao tác, kiểm tra cả hai.
 * Các liên kết thêm/sửa/xóa trỏ đến các file cùng thư mục.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/PostController.php";

requireManager();

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

$controller = new PostController($pdo);

/*
|--------------------------------------------------------------------------
| LẤY BÀI VIẾT
|--------------------------------------------------------------------------
*/

$posts = $controller->index();

/*
|--------------------------------------------------------------------------
| THÔNG BÁO
|--------------------------------------------------------------------------
*/

$message = $_GET['message'] ?? '';
$error   = $_GET['error'] ?? '';

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
        Quản lý bài viết | NHAT TRAN
    </title>

    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/posts/posts.css">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">

<!-- =====================================================
     HEADER
===================================================== -->

<?php require __DIR__ . '/../includes/shell-start.php'; ?>

<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Quản lý bài viết
        </h1>

        <div class="page-actions">

            <a
                href="../dashboard.php"
                class="btn btn-dashboard"
            >
                <?= adminIcon('back') ?> Dashboard
            </a>

            <a
                href="post-create.php"
                class="btn btn-primary"
            >
                <?= adminIcon('plus') ?> Thêm bài viết
            </a>

        </div>

    </div>


    <!-- MESSAGE -->

    <?php if ($message): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert alert-error">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- SUMMARY -->

    <div class="summary">

        Tổng số bài viết:

        <strong>
            <?= count($posts) ?>
        </strong>

        bài viết

    </div>


    <!-- TABLE -->

    <div class="table-card">


        <!-- DESKTOP -->

        <div class="table-wrapper desktop-table">

            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Tiêu đề
                        </th>

                        <th>
                            Mô tả ngắn
                        </th>

                        <th>
                            Trạng thái
                        </th>

                        <th>
                            Thứ tự
                        </th>

                        <th>
                            Ngày tạo
                        </th>

                        <th>
                            Thao tác
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (!empty($posts)): ?>

                    <?php foreach ($posts as $post): ?>

                        <?php

                        $status =
                            $post['status'] ?? 'draft';

                        $statusName =
                            match ($status) {

                                'published'
                                    => 'Đã xuất bản',

                                'inactive'
                                    => 'Ngừng hiển thị',

                                default
                                    => 'Bản nháp'

                            };

                        ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="post-id">

                                    #<?= (int) $post['id'] ?>

                                </span>

                            </td>


                            <!-- TITLE -->

                            <td>

                                <div class="post-title">

                                    <?= htmlspecialchars(
                                        $post['title']
                                            ?? 'Chưa có tiêu đề',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>


                                <?php if (!empty($post['slug'])): ?>

                                    <div class="post-slug">

                                        /
                                        <?= htmlspecialchars(
                                            $post['slug'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- EXCERPT -->

                            <td>

                                <div class="post-excerpt">

                                    <?= htmlspecialchars(
                                        $post['excerpt']
                                            ?? '—',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="status status-<?= htmlspecialchars(
                                    $status,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                                    <?= $statusName ?>

                                </span>

                            </td>


                            <!-- SORT -->

                            <td>

                                <?= (int) (
                                    $post['sort_order']
                                    ?? 0
                                ) ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $post['created_at']
                                    )
                                ) {

                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $post['created_at']
                                        )
                                    );

                                } else {

                                    echo '—';

                                }

                                ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="actions">

                                    <a
                                        href="post-edit.php?id=<?= (int) $post['id'] ?>"
                                        class="btn btn-edit"
                                    >
                                        <?= adminIcon('edit') ?> Sửa
                                    </a>

                                    <?= adminPostAction('post-delete.php?id=' . ((int) $post['id']), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                                </div>

                            </td>


                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="empty"
                        >
                            Chưa có bài viết nào.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- MOBILE -->

        <div class="mobile-list">

            <?php if (!empty($posts)): ?>

                <?php foreach ($posts as $post): ?>

                    <?php

                    $status =
                        $post['status'] ?? 'draft';

                    $statusName =
                        match ($status) {

                            'published'
                                => 'Đã xuất bản',

                            'inactive'
                                => 'Ngừng hiển thị',

                            default
                                => 'Bản nháp'

                        };

                    ?>

                    <div class="mobile-card">


                        <div class="mobile-top">

                            <div>

                                <div class="mobile-title">

                                    <?= htmlspecialchars(
                                        $post['title']
                                            ?? 'Chưa có tiêu đề',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>


                                <?php if (!empty($post['slug'])): ?>

                                    <div class="mobile-slug">

                                        /
                                        <?= htmlspecialchars(
                                            $post['slug'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="mobile-id">

                                #<?= (int) $post['id'] ?>

                            </div>

                        </div>


                        <div class="mobile-excerpt">

                            <?= htmlspecialchars(
                                $post['excerpt']
                                    ?? '—',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>


                        <div class="mobile-info">


                            <div>

                                <span class="mobile-label">
                                    Trạng thái
                                </span>

                                <span class="status status-<?= htmlspecialchars(
                                    $status,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                                    <?= $statusName ?>

                                </span>

                            </div>


                            <div>

                                <span class="mobile-label">
                                    Thứ tự
                                </span>

                                <div class="mobile-value">

                                    <?= (int) (
                                        $post['sort_order']
                                        ?? 0
                                    ) ?>

                                </div>

                            </div>


                            <div>

                                <span class="mobile-label">
                                    Ngày tạo
                                </span>

                                <div class="mobile-value">

                                    <?php

                                    if (
                                        !empty(
                                            $post['created_at']
                                        )
                                    ) {

                                        echo date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $post['created_at']
                                            )
                                        );

                                    } else {

                                        echo '—';

                                    }

                                    ?>

                                </div>

                            </div>


                        </div>


                        <div class="mobile-actions">

                            <a
                                href="post-edit.php?id=<?= (int) $post['id'] ?>"
                                class="btn btn-edit"
                            >
                                <?= adminIcon('edit') ?> Sửa
                            </a>

                            <?= adminPostAction('post-delete.php?id=' . ((int) $post['id']), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                        </div>


                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty">

                    Chưa có bài viết nào.

                </div>

            <?php endif; ?>

        </div>


    </div>


</div>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
