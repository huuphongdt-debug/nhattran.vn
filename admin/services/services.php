<?php
/*
 * Danh sách dịch vụ lấy qua ServiceController::index().
 * Bảng desktop và thẻ mobile dùng chung dữ liệu; khi thay trường/thao tác cần kiểm tra cả hai.
 * Phần ánh xạ icon trong file phục vụ hiển thị biểu tượng dịch vụ.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Controllers/ServiceController.php";

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

$controller = new ServiceController($pdo);


/*
|--------------------------------------------------------------------------
| LẤY DỊCH VỤ
|--------------------------------------------------------------------------
*/

$services = $controller->index();

/*
|--------------------------------------------------------------------------
| HIỂN THỊ ICON
|--------------------------------------------------------------------------
| Dịch vụ cũ có thể dùng emoji; các dịch vụ mới dùng class Font Awesome
| (ví dụ: fa-gears). Hai kiểu đều được hỗ trợ trong danh sách quản trị.
*/
$serviceIconMarkup = static function (string $icon): string {
    $icon = trim($icon);

    if (preg_match('/^fa-[a-z0-9-]+$/', $icon)) {
        return '<i class="fa-solid ' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>';
    }

    return htmlspecialchars($icon !== '' ? $icon : '⚙️', ENT_QUOTES, 'UTF-8');
};


/*
|--------------------------------------------------------------------------
| THÔNG BÁO
|--------------------------------------------------------------------------
*/

$message =
    $_GET['message'] ?? '';

$error =
    $_GET['error'] ?? '';

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
        Quản lý dịch vụ | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <link rel="stylesheet" href="../assets/css/services/services.css">

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
            Quản lý dịch vụ
        </h1>


        <div class="page-actions">

            <a
                href="../dashboard.php"
                class="btn btn-dashboard"
            >
                <?= adminIcon('back') ?> Dashboard
            </a>


            <a
                href="services-create.php"
                class="btn btn-primary"
            >
                <?= adminIcon('plus') ?> Thêm dịch vụ
            </a>

        </div>

    </div>


    <!-- =================================================
         THÔNG BÁO
    ================================================== -->

    <?php if ($message !== ''): ?>

        <div class="message">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         SERVICE BOX
    ================================================== -->

    <div class="table-box">


        <!-- SUMMARY -->

        <div class="table-summary">

            Tổng số dịch vụ:

            <strong>
                <?= count($services) ?>
            </strong>

        </div>


        <!-- =================================================
             KHÔNG CÓ DỊCH VỤ
        ================================================== -->

        <?php if (empty($services)): ?>

            <div class="empty">

                Chưa có dịch vụ.

                <br><br>

                <a
                    href="services-create.php"
                    class="btn btn-primary"
                >
                    <?= adminIcon('plus') ?> Thêm dịch vụ đầu tiên
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

                            <th>ID</th>

                            <th>Icon</th>

                            <th>Tên dịch vụ</th>

                            <th>Mô tả ngắn</th>

                            <th>Trạng thái</th>

                            <th>Thứ tự</th>

                            <th>Ngày tạo</th>

                            <th>Thao tác</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($services as $service): ?>

                            <?php

                            $serviceId =
                                (int) (
                                    $service['id']
                                    ?? 0
                                );

                            $serviceName =
                                $service['name']
                                ?? 'Chưa đặt tên';

                            $icon =
                                trim(
                                    $service['icon']
                                    ?? ''
                                );

                            $description =
                                trim(
                                    $service['short_description']
                                    ?? ''
                                );

                            $status =
                                $service['status']
                                ?? 'inactive';

                            $sortOrder =
                                (int) (
                                    $service['sort_order']
                                    ?? 0
                                );

                            $createdAt =
                                $service['created_at']
                                ?? '';

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= $serviceId ?>

                                </td>


                                <!-- ICON -->

                                <td>

                                    <div class="service-icon">

                                        <?= $serviceIconMarkup($icon) ?>

                                    </div>

                                </td>


                                <!-- TÊN -->

                                <td>

                                    <div class="service-name">

                                        <?= htmlspecialchars(
                                            $serviceName
                                        ) ?>

                                    </div>

                                </td>


                                <!-- MÔ TẢ -->

                                <td>

                                    <div class="service-description">

                                        <?= $description !== ''
                                            ? htmlspecialchars($description)
                                            : '—'
                                        ?>

                                    </div>

                                </td>


                                <!-- TRẠNG THÁI -->

                                <td>

                                    <?php if (
                                        $status === 'active'
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

                                    <span class="sort-order">

                                        <?= $sortOrder ?>

                                    </span>

                                </td>


                                <!-- NGÀY TẠO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $createdAt
                                    ) ?>

                                </td>


                                <!-- THAO TÁC -->

                                <td class="admin-actions-cell">

                                    <div class="actions">

                                        <a
                                            href="services-edit.php?id=<?= $serviceId ?>"
                                            class="btn btn-edit"
                                        >
                                            <?= adminIcon('edit') ?> Sửa
                                        </a>


                                        <?= adminPostAction('services-delete.php?id=' . ($serviceId), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 MOBILE SERVICE CARDS
            ================================================== -->

            <div class="mobile-service-list">

                <?php foreach ($services as $service): ?>

                    <?php

                    $serviceId =
                        (int) (
                            $service['id']
                            ?? 0
                        );

                    $serviceName =
                        $service['name']
                        ?? 'Chưa đặt tên';

                    $icon =
                        trim(
                            $service['icon']
                            ?? ''
                        );

                    $description =
                        trim(
                            $service['short_description']
                            ?? ''
                        );

                    $status =
                        $service['status']
                        ?? 'inactive';

                    $sortOrder =
                        (int) (
                            $service['sort_order']
                            ?? 0
                        );

                    ?>


                    <div class="mobile-service-card">


                        <!-- HEADER -->

                        <div class="mobile-service-header">

                            <div class="mobile-service-title">

                                <div class="mobile-service-icon">

                                    <?= $serviceIconMarkup($icon) ?>

                                </div>


                                <div class="mobile-service-name">

                                    <?= htmlspecialchars(
                                        $serviceName
                                    ) ?>

                                </div>

                            </div>


                            <span class="mobile-service-id">

                                #<?= $serviceId ?>

                            </span>

                        </div>


                        <!-- DESCRIPTION -->

                        <?php if ($description !== ''): ?>

                            <div class="mobile-service-description">

                                <?= htmlspecialchars(
                                    $description
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <!-- INFO -->

                        <div class="mobile-service-info">


                            <div>

                                <?php if (
                                    $status === 'active'
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


                            <div class="mobile-service-order">

                                Thứ tự:

                                <strong>
                                    <?= $sortOrder ?>
                                </strong>

                            </div>

                        </div>


                        <!-- ACTIONS -->

                        <div class="mobile-service-actions">

                            <a
                                href="services-edit.php?id=<?= $serviceId ?>"
                                class="btn btn-edit"
                            >
                                <?= adminIcon('edit') ?> Sửa
                            </a>


                            <?= adminPostAction('services-delete.php?id=' . ($serviceId), (adminIcon('trash')) . ' Xóa', 'btn btn-delete', true) ?>

                        </div>


                    </div>

                <?php endforeach; ?>

            </div>


        <?php endif; ?>


    </div>


</main>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
