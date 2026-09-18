<?php

/*
 * Danh sách danh mục sản phẩm: nhận bộ lọc GET, gọi controller và xuất giao diện.
 * ProductCategoryController cung cấp items, total, page và total_pages.
 * Bảng desktop và thẻ mobile bên dưới dùng chung $categories;
 * khi sửa trường hiển thị hoặc liên kết thao tác, cần kiểm tra cả hai phần.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ProductCategoryController.php";

requireManager();
// Phân quyền trước khi đọc dữ liệu; shell-start.php cung cấp khung trang và icon chung.

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
| THÔNG BÁO
|--------------------------------------------------------------------------
*/

$message = $_GET['message'] ?? '';
$error   = $_GET['error'] ?? '';


/*
|--------------------------------------------------------------------------
| CONTROLLER
|--------------------------------------------------------------------------
*/

$controller = new ProductCategoryController($pdo);


/*
|--------------------------------------------------------------------------
| BỘ LỌC
|--------------------------------------------------------------------------
*/

$keyword = trim($_GET['keyword'] ?? '');

$type = $_GET['type'] ?? 'all';

$status = $_GET['status'] ?? 'all';

$page = max(
    1,
    (int) ($_GET['page'] ?? 1)
);


/*
|--------------------------------------------------------------------------
| SỐ DANH MỤC MỖI TRANG
|--------------------------------------------------------------------------
*/

$perPage = 5;
// Thay đổi số mục mỗi trang tại đây; tổng số trang lấy từ kết quả controller.


/*
|--------------------------------------------------------------------------
| LẤY DANH SÁCH
|--------------------------------------------------------------------------
*/

$result = $controller->index(
    $keyword,
    $type,
    $status,
    $page,
    $perPage
);


/*
|--------------------------------------------------------------------------
| DỮ LIỆU
|--------------------------------------------------------------------------
*/

$categories = $result['items'] ?? [];

$total = (int) (
    $result['total'] ?? 0
);

$currentPage = (int) (
    $result['page'] ?? 1
);

$totalPages = (int) (
    $result['total_pages'] ?? 1
);


/*
|--------------------------------------------------------------------------
| TẠO DANH SÁCH TRANG
|--------------------------------------------------------------------------
*/

// Tối đa 7 trang: hiện đầy đủ. Nhiều hơn: giữ trang đầu/cuối và các trang gần trang hiện tại.
// Giá trị '...' chỉ là dấu phân cách, không phải số trang để tạo liên kết.
$paginationPages = [];

if ($totalPages <= 7) {

    for (
        $i = 1;
        $i <= $totalPages;
        $i++
    ) {

        $paginationPages[] = $i;
    }

} else {

    /*
    |--------------------------------------------------------------------------
    | TRANG ĐẦU
    |--------------------------------------------------------------------------
    */

    $paginationPages[] = 1;


    /*
    |--------------------------------------------------------------------------
    | GẦN ĐẦU
    |--------------------------------------------------------------------------
    */

    if ($currentPage <= 4) {

        $paginationPages[] = 2;
        $paginationPages[] = 3;
        $paginationPages[] = 4;
        $paginationPages[] = 5;

        $paginationPages[] = '...';

        $paginationPages[] = $totalPages;


    /*
    |--------------------------------------------------------------------------
    | GẦN CUỐI
    |--------------------------------------------------------------------------
    */

    } elseif (
        $currentPage >= $totalPages - 3
    ) {

        $paginationPages[] = '...';

        $paginationPages[] = $totalPages - 4;
        $paginationPages[] = $totalPages - 3;
        $paginationPages[] = $totalPages - 2;
        $paginationPages[] = $totalPages - 1;
        $paginationPages[] = $totalPages;


    /*
    |--------------------------------------------------------------------------
    | Ở GIỮA
    |--------------------------------------------------------------------------
    */

    } else {

        $paginationPages[] = '...';

        $paginationPages[] = $currentPage - 1;
        $paginationPages[] = $currentPage;
        $paginationPages[] = $currentPage + 1;

        $paginationPages[] = '...';

        $paginationPages[] = $totalPages;
    }
}


/*
|--------------------------------------------------------------------------
| QUERY GIỮ BỘ LỌC
|--------------------------------------------------------------------------
*/

// Các liên kết phân trang ghép thêm page vào bộ lọc này bằng http_build_query().
// Liên kết di chuyển danh mục cũng truyền bộ lọc để quay lại đúng ngữ cảnh.
$filterParams = [

    'keyword' => $keyword,

    'type' => $type,

    'status' => $status

];

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
        Danh mục sản phẩm | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/categories/categories.css">

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


    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="page-header">

        <h1>
            Danh mục sản phẩm
        </h1>


        <div class="page-actions">

            <a
                href="../dashboard.php"
                class="btn btn-dashboard"
            >
                <?= adminIcon('back') ?> Dashboard
            </a>


            <a
                href="category-create.php"
                class="btn btn-primary"
            >
                <?= adminIcon('plus') ?> Thêm danh mục
            </a>

        </div>

    </div>


    <!-- =================================================
         FILTER
    ================================================== -->

    <div class="filter-box">

        <form method="GET">


            <!-- TÌM KIẾM -->

            <div class="filter-group">

                <label for="keyword">
                    Tìm kiếm
                </label>

                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    value="<?= htmlspecialchars($keyword) ?>"
                    placeholder="Tên danh mục hoặc slug..."
                >

            </div>


            <!-- LOẠI -->

            <div class="filter-group">

                <label for="type">
                    Loại danh mục
                </label>

                <select
                    id="type"
                    name="type"
                >

                    <option
                        value="all"
                        <?= $type === 'all'
                            ? 'selected'
                            : '' ?>
                    >
                        Tất cả danh mục
                    </option>


                    <option
                        value="parent"
                        <?= $type === 'parent'
                            ? 'selected'
                            : '' ?>
                    >
                        Danh mục cha
                    </option>


                    <option
                        value="child"
                        <?= $type === 'child'
                            ? 'selected'
                            : '' ?>
                    >
                        Danh mục con
                    </option>

                </select>

            </div>


            <!-- TRẠNG THÁI -->

            <div class="filter-group">

                <label for="status">
                    Trạng thái
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="all"
                        <?= $status === 'all'
                            ? 'selected'
                            : '' ?>
                    >
                        Tất cả trạng thái
                    </option>


                    <option
                        value="active"
                        <?= $status === 'active'
                            ? 'selected'
                            : '' ?>
                    >
                        Hoạt động
                    </option>


                    <option
                        value="inactive"
                        <?= $status === 'inactive'
                            ? 'selected'
                            : '' ?>
                    >
                        Tạm ẩn
                    </option>

                </select>

            </div>


            <!-- BUTTON -->

            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= adminIcon('search') ?> Tìm kiếm
                </button>


                <a
                    href="categories.php"
                    class="btn btn-dashboard"
                >
                    <?= adminIcon('reset') ?> Xóa lọc
                </a>

            </div>

        </form>

    </div>


    <!-- =================================================
         ALERT SUCCESS
    ================================================== -->

    <?php if ($message !== ''): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ALERT ERROR
    ================================================== -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         CATEGORY BOX
    ================================================== -->

    <div class="table-box">


        <?php if (empty($categories)): ?>


            <!-- KHÔNG CÓ DỮ LIỆU -->

            <div class="empty">

                Chưa có danh mục sản phẩm.

                <br>

                <a
                    href="category-create.php"
                    class="btn btn-primary"
                >
                    <?= adminIcon('plus') ?> Thêm danh mục đầu tiên
                </a>

            </div>


        <?php else: ?>


            <!-- =================================================
                 DESKTOP TABLE
            ================================================== -->

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Tên danh mục
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                Trạng thái
                            </th>

                            <th>
                                Thứ tự
                            </th>

                            <th>
                                Vị trí
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


                        <?php foreach (
                            $categories
                            as $category
                        ): ?>


                            <?php

                            $categoryId =
                                (int) (
                                    $category['id']
                                    ?? 0
                                );

                            $categoryName =
                                $category['name']
                                ?? 'Chưa đặt tên';

                            $categorySlug =
                                $category['slug']
                                ?? '';

                            $categoryStatus =
                                $category['status']
                                ?? 'inactive';

                            $sortOrder =
                                (int) (
                                    $category['sort_order']
                                    ?? 0
                                );

                            $createdAt =
                                $category['created_at']
                                ?? '';

                            $parentId =
                                $category['parent_id']
                                ?? null;

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= $categoryId ?>

                                </td>


                                <!-- TÊN -->

                                <td>

                                    <?php if (
                                        $parentId === null
                                    ): ?>

                                        <div
                                            class="category-name parent"
                                        >

                                            <?= htmlspecialchars(
                                                $categoryName
                                            ) ?>

                                        </div>

                                    <?php else: ?>

                                        <div
                                            class="category-name child"
                                        >

                                            <?= htmlspecialchars(
                                                $categoryName
                                            ) ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- SLUG -->

                                <td>

                                    <?php if (
                                        $categorySlug !== ''
                                    ): ?>

                                        <span class="slug">

                                            <?= htmlspecialchars(
                                                $categorySlug
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span
                                            style="color:#9ca3af;"
                                        >
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if (
                                        $categoryStatus === 'active'
                                    ): ?>

                                        <span
                                            class="status status-active"
                                        >
                                            Hoạt động
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="status status-inactive"
                                        >
                                            Tạm ẩn
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- THỨ TỰ -->

                                <td>

                                    <?= $sortOrder ?>

                                </td>


                                <!-- VỊ TRÍ -->

                                <td>

                                    <div class="move-actions">


                                        <!-- LÊN -->

                                        <?= adminPostAction('category-move.php?' . (http_build_query([
                                                'id' =>
                                                    $categoryId,

                                                'direction' =>
                                                    'up',

                                                'keyword' =>
                                                    $keyword,

                                                'type' =>
                                                    $type,

                                                'status' =>
                                                    $status,

                                                'page' =>
                                                    $currentPage
                                            ])), '▲', 'btn btn-move', false) ?>


                                        <!-- XUỐNG -->

                                        <?= adminPostAction('category-move.php?' . (http_build_query([
                                                'id' =>
                                                    $categoryId,

                                                'direction' =>
                                                    'down',

                                                'keyword' =>
                                                    $keyword,

                                                'type' =>
                                                    $type,

                                                'status' =>
                                                    $status,

                                                'page' =>
                                                    $currentPage
                                            ])), '▼', 'btn btn-move', false) ?>

                                    </div>

                                </td>


                                <!-- NGÀY TẠO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $createdAt
                                    ) ?>

                                </td>


                                <!-- THAO TÁC -->

                                <td>

                                    <div class="actions">


                                        <a
                                            href="category-edit.php?id=<?= $categoryId ?>"
                                            class="btn btn-edit"
                                        >
                                            <?= adminIcon('edit') ?> Sửa
                                        </a>


                                        <?= adminPostAction('category-delete.php?id=' . ($categoryId), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 MOBILE CATEGORY CARDS
            ================================================== -->

            <div class="mobile-category-list">


                <?php foreach (
                    $categories
                    as $category
                ): ?>


                    <?php

                    $categoryId =
                        (int) (
                            $category['id']
                            ?? 0
                        );

                    $categoryName =
                        $category['name']
                        ?? 'Chưa đặt tên';

                    $categorySlug =
                        $category['slug']
                        ?? '';

                    $categoryStatus =
                        $category['status']
                        ?? 'inactive';

                    $sortOrder =
                        (int) (
                            $category['sort_order']
                            ?? 0
                        );

                    $parentId =
                        $category['parent_id']
                        ?? null;

                    ?>


                    <div
                        class="mobile-category-card"
                    >


                        <!-- TÊN + ID -->

                        <div
                            class="mobile-category-header"
                        >


                            <div
                                class="mobile-category-name"
                            >

                                <?php if (
                                    $parentId === null
                                ): ?>

                                    <?= htmlspecialchars(
                                        $categoryName
                                    ) ?>

                                <?php else: ?>

                                    <span
                                        class="mobile-child-icon"
                                    >
                                        └─
                                    </span>

                                    <?= htmlspecialchars(
                                        $categoryName
                                    ) ?>

                                <?php endif; ?>

                            </div>


                            <span
                                class="mobile-category-id"
                            >
                                #<?= $categoryId ?>
                            </span>

                        </div>


                        <!-- SLUG -->

                        <?php if (
                            $categorySlug !== ''
                        ): ?>

                            <div
                                class="mobile-category-slug"
                            >

                                <?= htmlspecialchars(
                                    $categorySlug
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <!-- STATUS + SORT -->

                        <div
                            class="mobile-category-info"
                        >


                            <div>

                                <?php if (
                                    $categoryStatus === 'active'
                                ): ?>

                                    <span
                                        class="status status-active"
                                    >
                                        Hoạt động
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status status-inactive"
                                    >
                                        Tạm ẩn
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div
                                class="mobile-category-order"
                            >

                                Thứ tự:

                                <strong>
                                    <?= $sortOrder ?>
                                </strong>

                            </div>

                        </div>


                        <!-- SỬA + XÓA -->

                        <div
                            class="mobile-category-actions"
                        >


                            <a
                                href="category-edit.php?id=<?= $categoryId ?>"
                                class="btn btn-edit"
                            >
                                <?= adminIcon('edit') ?> Sửa
                            </a>


                            <?= adminPostAction('category-delete.php?id=' . ($categoryId), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                        </div>


                        <!-- DI CHUYỂN -->

                        <div
                            class="mobile-category-move"
                        >


                            <?= adminPostAction('category-move.php?' . (http_build_query([
                                    'id' =>
                                        $categoryId,

                                    'direction' =>
                                        'up',

                                    'keyword' =>
                                        $keyword,

                                    'type' =>
                                        $type,

                                    'status' =>
                                        $status,

                                    'page' =>
                                        $currentPage
                                ])), '▲ Di chuyển lên', 'btn btn-move', false) ?>


                            <?= adminPostAction('category-move.php?' . (http_build_query([
                                    'id' =>
                                        $categoryId,

                                    'direction' =>
                                        'down',

                                    'keyword' =>
                                        $keyword,

                                    'type' =>
                                        $type,

                                    'status' =>
                                        $status,

                                    'page' =>
                                        $currentPage
                                ])), '▼ Di chuyển xuống', 'btn btn-move', false) ?>

                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


        <!-- =================================================
             PAGINATION
        ================================================== -->

        <?php if (
            $totalPages > 1
        ): ?>


            <div class="pagination">


                <!-- TRANG TRƯỚC -->

                <?php if (
                    $currentPage > 1
                ): ?>

                    <a
                        href="?<?= http_build_query(
                            array_merge(
                                $filterParams,
                                [
                                    'page' =>
                                        $currentPage - 1
                                ]
                            )
                        ) ?>"
                    >
                        <?= adminIcon('back') ?> Trước
                    </a>

                <?php else: ?>

                    <span class="disabled">
                        <?= adminIcon('back') ?> Trước
                    </span>

                <?php endif; ?>


                <!-- SỐ TRANG -->

                <?php foreach (
                    $paginationPages
                    as $pageNumber
                ): ?>


                    <?php if (
                        $pageNumber === '...'
                    ): ?>

                        <span class="disabled">
                            ...
                        </span>


                    <?php elseif (
                        $pageNumber === $currentPage
                    ): ?>

                        <span class="active">
                            <?= $pageNumber ?>
                        </span>


                    <?php else: ?>

                        <a
                            href="?<?= http_build_query(
                                array_merge(
                                    $filterParams,
                                    [
                                        'page' =>
                                            $pageNumber
                                    ]
                                )
                            ) ?>"
                        >
                            <?= $pageNumber ?>
                        </a>

                    <?php endif; ?>


                <?php endforeach; ?>


                <!-- TRANG SAU -->

                <?php if (
                    $currentPage < $totalPages
                ): ?>

                    <a
                        href="?<?= http_build_query(
                            array_merge(
                                $filterParams,
                                [
                                    'page' =>
                                        $currentPage + 1
                                ]
                            )
                        ) ?>"
                    >
                        Sau <?= adminIcon('arrow') ?>
                    </a>

                <?php else: ?>

                    <span class="disabled">
                        Sau <?= adminIcon('arrow') ?>
                    </span>

                <?php endif; ?>


            </div>


            <!-- THÔNG TIN -->

            <div class="pagination-info">

                Trang
                <?= $currentPage ?>
                /
                <?= $totalPages ?>

                —

                Tổng
                <?= $total ?>

                danh mục

            </div>


        <?php endif; ?>


    </div>


</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
