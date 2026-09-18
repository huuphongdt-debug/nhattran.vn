<?php
/*
 * Danh sách hãng: tìm theo tên/slug, lọc nhóm và hiển thị số sản phẩm liên quan.
 * groupFilter: 0 là tất cả, -1 là chưa phân nhóm, số dương là ID nhóm.
 * Dùng EXISTS để lọc nhóm, tránh nhân số lượng sản phẩm khi hãng thuộc nhiều nhóm.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";

/*
|--------------------------------------------------------------------------
| KIỂM TRA ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/

requireLogin();
require_once __DIR__ . '/group-bootstrap.php';
$groupFilter = (int) ($_GET['group'] ?? 0);
$manufacturerMemberships = $groupModel->memberships();


/*
|--------------------------------------------------------------------------
| THÔNG TIN USER
|--------------------------------------------------------------------------
*/

$user = currentUser();

$fullName =
    $user['full_name']
    ?? 'Administrator';

$role =
    $user['role']
    ?? 'manager';


/*
|--------------------------------------------------------------------------
| HÀM ESCAPE HTML
|--------------------------------------------------------------------------
*/

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| TÌM KIẾM
|--------------------------------------------------------------------------
*/

$keyword =
    trim(
        $_GET['keyword']
        ?? ''
    );


/*
|--------------------------------------------------------------------------
| LẤY DANH SÁCH HÃNG SẢN XUẤT
|--------------------------------------------------------------------------
|
| LEFT JOIN products để biết mỗi hãng
| đang được bao nhiêu sản phẩm sử dụng.
|
*/

$manufacturers = [];

$error = '';

try {

    $sql = "
        SELECT
            m.id,
            m.name,
            m.slug,
            m.image,
            m.created_at,

            COUNT(p.id) AS product_count

        FROM manufacturers AS m

        LEFT JOIN products AS p
            ON p.manufacturer_id = m.id
    ";

    $params = [];

    /*
    |--------------------------------------------------------------------------
    | TÌM KIẾM
    |--------------------------------------------------------------------------
    */

    $conditions = [];
    if ($keyword !== '') {

        $conditions[] = '(m.name LIKE :keyword OR m.slug LIKE :keyword)';

        $params[':keyword'] =
            '%' . $keyword . '%';
    }
    if ($groupFilter > 0) {
        $conditions[] = 'EXISTS (SELECT 1 FROM manufacturer_group_members gm WHERE gm.manufacturer_id=m.id AND gm.group_id=:group_id)';
        $params[':group_id'] = $groupFilter;
    } elseif ($groupFilter === -1) {
        $conditions[] = 'NOT EXISTS (SELECT 1 FROM manufacturer_group_members gm WHERE gm.manufacturer_id=m.id)';
    }
    if ($conditions) $sql .= ' WHERE ' . implode(' AND ', $conditions);


    /*
    |--------------------------------------------------------------------------
    | GROUP
    |--------------------------------------------------------------------------
    */

    $sql .= "
        GROUP BY
            m.id,
            m.name,
            m.slug,
            m.image,
            m.created_at

        ORDER BY
            m.name ASC,
            m.id DESC
    ";


    /*
    |--------------------------------------------------------------------------
    | QUERY
    |--------------------------------------------------------------------------
    */

    $stmt =
        $pdo->prepare($sql);

    $stmt->execute(
        $params
    );

    $manufacturers =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

} catch (PDOException $e) {

    $error =
        'Không thể tải danh sách hãng sản xuất.';
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
        Quản lý hãng sản xuất | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/manufacturers/manufacturers.css">

<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/manufacturers/groups.css">
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

            Quản lý hãng sản xuất

        </h1>


        <div class="page-actions">
            <a href="groups.php" class="btn btn-dashboard"><?= adminIcon('category') ?> Danh mục hãng</a>

            <a
                href="../dashboard.php"
                class="btn btn-dashboard"
            >

                <?= adminIcon('back') ?> Dashboard

            </a>


            <a
                href="manufacturers-create.php"
                class="btn btn-primary"
            >

                <?= adminIcon('plus') ?> Thêm hãng

            </a>

        </div>

    </div>



    <?php if ($error !== ''): ?>

        <div class="error">

            <?= e($error) ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         SEARCH
    ================================================== -->

    <div class="filter-box">

        <form
            method="GET"
            action="manufacturers.php"
            class="filter-form"
        >

            <div class="filter-group">

                <label for="keyword">

                    Tìm kiếm hãng sản xuất

                </label>


                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    class="filter-input"
                    value="<?= e($keyword) ?>"
                    placeholder="Nhập tên hãng sản xuất..."
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >

                <?= adminIcon('search') ?> Tìm kiếm

            </button>


            <div class="filter-group manufacturer-group-filter">
                <label for="group">Danh mục hãng</label>
                <select id="group" name="group" class="filter-input">
                    <option value="0">Tất cả danh mục</option>
                    <option value="-1" <?= $groupFilter === -1 ? 'selected' : '' ?>>Chưa phân nhóm</option>
                    <?php foreach ($manufacturerGroups as $group): ?>
                    <option value="<?= (int) $group['id'] ?>" <?= $groupFilter === (int) $group['id'] ? 'selected' : '' ?>><?= e($group['name']) ?> (<?= (int) $group['total'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($keyword !== '' || $groupFilter !== 0): ?>

                <a
                    href="manufacturers.php"
                    class="btn btn-dashboard"
                >

                    <?= adminIcon('reset') ?> Xóa lọc

                </a>

            <?php endif; ?>

        </form>

    </div>



    <!-- =================================================
         TABLE
    ================================================== -->

    <div class="table-box">


        <div class="table-header">

            <h2>

                Danh sách hãng sản xuất

            </h2>


            <span>

                <?= count($manufacturers) ?>
                hãng

            </span>

        </div>



        <?php if (empty($manufacturers)): ?>


            <div class="empty">

                <div class="empty-icon">

                    <?= adminIcon('building') ?>

                </div>


                <h3>

                    Chưa có hãng sản xuất

                </h3>


                <p>

                    Hãy thêm hãng sản xuất đầu tiên.

                </p>


                <br>


                <a
                    href="manufacturers-create.php"
                    class="btn btn-primary"
                >

                    <?= adminIcon('plus') ?> Thêm hãng sản xuất

                </a>

            </div>


        <?php else: ?>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Hãng sản xuất
                            </th>

                            <th>
                                Logo
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                Số sản phẩm
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
                        $manufacturers
                        as $manufacturer
                    ): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?= (int) $manufacturer['id'] ?>

                            </td>



                            <!-- NAME -->

                            <td>

                                <div
                                    class="manufacturer-name"
                                >

                                    <?= e(
                                        $manufacturer['name']
                                    ) ?>

                                </div>
                                <div class="manufacturer-group-tags">
                                <?php foreach ($manufacturerMemberships[(int) $manufacturer['id']] ?? [] as $group): ?>
                                    <a href="?<?= e(http_build_query(['group' => $group['id'], 'keyword' => $keyword])) ?>"><?= e($group['name']) ?></a>
                                <?php endforeach; ?>
                                <?php if (empty($manufacturerMemberships[(int) $manufacturer['id']])): ?><small>Chưa phân nhóm</small><?php endif; ?>
                                </div>

                            </td>



                            <!-- LOGO -->

                            <td>

                                <?php if (!empty($manufacturer['image'])): ?>

                                    <img
                                        src="<?= e($manufacturer['image']) ?>"
                                        alt="Logo <?= e($manufacturer['name']) ?>"
                                        class="manufacturer-logo"
                                    >

                                <?php else: ?>

                                    <span style="color:#9ca3af;">â€”</span>

                                <?php endif; ?>

                            </td>


                            <!-- SLUG -->

                            <td>

                                <span class="slug">

                                    <?= e(
                                        $manufacturer['slug']
                                    ) ?>

                                </span>

                            </td>



                            <!-- PRODUCT COUNT -->

                            <td>

                                <span
                                    class="product-count"
                                >

                                    <?= (int) (
                                        $manufacturer['product_count']
                                        ?? 0
                                    ) ?>

                                </span>

                            </td>



                            <!-- CREATED -->

                            <td>

                                <?php

                                $createdAt =
                                    $manufacturer['created_at']
                                    ?? '';

                                if ($createdAt !== '') {

                                    echo e(
                                        date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $createdAt
                                            )
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
                                        href="manufacturers-edit.php?id=<?= (int) $manufacturer['id'] ?>"
                                        class="action-btn btn-edit"
                                    >

                                        <?= adminIcon('edit') ?> Sửa

                                    </a>


                                    <form method="post" action="manufacturers-delete.php"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa hãng sản xuất này không?');">
                                    <input type="hidden" name="csrf" value="<?= e(adminCsrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $manufacturer['id'] ?>">
                                    <button type="submit"
                                        class="action-btn btn-delete"
                                    >

                                        <?= adminIcon('trash') ?> Xóa

                                    </button>
                                    </form>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>


</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
