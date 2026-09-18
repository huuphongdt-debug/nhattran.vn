<?php
/*
 * Thêm hãng: chuẩn hóa tên/slug, xử lý logo rồi lưu hãng và liên kết nhóm.
 * group-bootstrap.php kiểm tra nhóm/CSRF; group-fields.php là phần chọn nhóm dùng chung.
 * Transaction bao gồm INSERT hãng và sync nhóm; upload file là thao tác riêng.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Helpers/ManufacturerLogo.php";
require_once __DIR__ . '/group-bootstrap.php';

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
| BIẾN
|--------------------------------------------------------------------------
*/

$name  = '';
$slug  = '';
$image = null;

$error   = '';
$message = '';


/*
|--------------------------------------------------------------------------
| HÀM TẠO SLUG
|--------------------------------------------------------------------------
*/

function createSlug(string $text): string
{
    $text = trim($text);

    $text = mb_strtolower(
        $text,
        'UTF-8'
    );

    $text = preg_replace(
        '/[áàảãạăắằẳẵặâấầẩẫậ]/u',
        'a',
        $text
    );

    $text = preg_replace(
        '/[éèẻẽẹêếềểễệ]/u',
        'e',
        $text
    );

    $text = preg_replace(
        '/[íìỉĩị]/u',
        'i',
        $text
    );

    $text = preg_replace(
        '/[óòỏõọôốồổỗộơớờởỡợ]/u',
        'o',
        $text
    );

    $text = preg_replace(
        '/[úùủũụưứừửữự]/u',
        'u',
        $text
    );

    $text = preg_replace(
        '/[ýỳỷỹỵ]/u',
        'y',
        $text
    );

    $text = preg_replace(
        '/đ/u',
        'd',
        $text
    );

    $text = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $text
    );

    $text = trim(
        $text,
        '-'
    );

    return $text;
}


/*
|--------------------------------------------------------------------------
| XỬ LÝ FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim(
        $_POST['name'] ?? ''
    );

    $image = uploadManufacturerLogo(
        $_FILES['image'] ?? null,
        $error
    );


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA TÊN
    |--------------------------------------------------------------------------
    */

    if ($error !== '') {

        // Lỗi upload đã được gán trong helper.

    } elseif ($name === '') {

        $error =
            'Vui lòng nhập tên hãng sản xuất.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | TẠO SLUG
        |--------------------------------------------------------------------------
        */

        $slug = createSlug($name);


        if ($slug === '') {

            $error =
                'Không thể tạo slug từ tên hãng.';

        } else {

            try {

                /*
                |--------------------------------------------------------------------------
                | KIỂM TRA HÃNG ĐÃ TỒN TẠI
                |--------------------------------------------------------------------------
                */

                $check = $pdo->prepare(
                    "
                    SELECT id
                    FROM manufacturers
                    WHERE name = ?
                       OR slug = ?
                    LIMIT 1
                    "
                );

                $check->execute([
                    $name,
                    $slug
                ]);


                if ($check->fetch()) {

                    $error =
                        'Hãng sản xuất này đã tồn tại.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | THÊM HÃNG
                    |--------------------------------------------------------------------------
                    */

                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare(
                        "
                        INSERT INTO manufacturers
                        (
                            name,
                            slug,
                            image
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?
                        )
                        "
                    );

                    $stmt->execute([
                        $name,
                        $slug,
                        $image
                    ]);
                    $groupModel->sync((int) $pdo->lastInsertId(), $selectedGroups);
                    $pdo->commit();


                    /*
                    |--------------------------------------------------------------------------
                    | CHUYỂN VỀ DANH SÁCH
                    |--------------------------------------------------------------------------
                    */

                    header(
                        "Location: manufacturers.php?message="
                        . urlencode(
                            'Thêm hãng sản xuất thành công.'
                        )
                    );

                    exit;
                }

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();

                $error =
                    'Có lỗi xảy ra khi thêm hãng sản xuất.';
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
        Thêm hãng sản xuất | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/manufacturers/manufacturers-create.css">

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
            Thêm hãng sản xuất
        </h1>


        <a
            href="manufacturers.php"
            class="btn btn-back"
        >
            <?= adminIcon('back') ?> Danh sách hãng
        </a>

    </div>



    <!-- FORM -->

    <div class="form-box">


        <?php if ($error !== ''): ?>

            <div class="alert-error">

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>



        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
        >


            <!-- TÊN HÃNG -->

            <div class="form-group">

                <label for="name">

                    Hãng sản xuất
                    <span style="color:#dc2626;">
                        *
                    </span>

                </label>


                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Ví dụ: Mitsubishi"
                    required
                    autofocus
                >


                <div class="help-text">

                    Ví dụ: Mitsubishi, Siemens,
                    Omron, Panasonic, Schneider...

                </div>

            </div>


            <!-- LOGO HÃNG -->

            <div class="form-group">

                <label for="image">
                    Logo hãng
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div class="help-text">
                    Chấp nhận JPG, PNG hoặc WebP; dung lượng tối đa 2 MB.
                </div>

            </div>



            <!-- SLUG -->

            <div class="form-group">

                <label for="slug">

                    Slug

                </label>


                <input
                    type="text"
                    id="slug"
                    value="<?= htmlspecialchars(
                        $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Slug sẽ được tạo tự động"
                    readonly
                >


                <div class="help-text">

                    Slug được hệ thống tạo tự động
                    từ tên hãng.

                </div>

            </div>



            <!-- ACTION -->

            <?php require __DIR__ . '/views/group-fields.php'; ?>
            <div class="form-actions">

                <a
                    href="manufacturers.php"
                    class="btn btn-back"
                >
                    Hủy
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= adminIcon('plus') ?> Thêm hãng
                </button>

            </div>


        </form>

    </div>


</main>


<script src="../assets/js/manufacturers/manufacturers-create.js"></script>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
