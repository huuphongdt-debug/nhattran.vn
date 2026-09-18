<?php
/*
 * Sửa hãng theo ID trên URL; tải dữ liệu hiện tại trước khi xử lý POST.
 * ManufacturerLogo hỗ trợ logo; group-bootstrap.php chuẩn bị và kiểm tra danh mục hãng.
 * UPDATE hãng và sync nhóm cùng transaction, sau đó chuyển về danh sách.
 */


require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/ManufacturerLogo.php';
require_once __DIR__ . '/group-bootstrap.php';


// =====================================================
// LẤY ID HÃNG
// =====================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    header(
        'Location: manufacturers.php'
    );

    exit;
}


// =====================================================
// LẤY THÔNG TIN HÃNG
// =====================================================

$sql = "
    SELECT *
    FROM manufacturers
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$manufacturer = $stmt->fetch();


if (!$manufacturer) {

    header(
        'Location: manufacturers.php'
    );

    exit;
}


// =====================================================
// BIẾN HIỂN THỊ
// =====================================================

$name = $manufacturer['name'] ?? '';

$slug = $manufacturer['slug'] ?? '';
$image = $manufacturer['image'] ?? null;

$error = '';


// =====================================================
// HÀM TẠO SLUG
// =====================================================

function createSlug(string $text): string
{
    $text = trim($text);

    /*
     * Chuyển chữ thường
     */
    $text = mb_strtolower(
        $text,
        'UTF-8'
    );


    /*
     * Loại bỏ dấu tiếng Việt
     */
    $text = str_replace(
        [
            'à','á','ạ','ả','ã',
            'â','ầ','ấ','ậ','ẩ','ẫ',
            'ă','ằ','ắ','ặ','ẳ','ẵ'
        ],
        'a',
        $text
    );

    $text = str_replace(
        [
            'è','é','ẹ','ẻ','ẽ',
            'ê','ề','ế','ệ','ể','ễ'
        ],
        'e',
        $text
    );

    $text = str_replace(
        [
            'ì','í','ị','ỉ','ĩ'
        ],
        'i',
        $text
    );

    $text = str_replace(
        [
            'ò','ó','ọ','ỏ','õ',
            'ô','ồ','ố','ộ','ổ','ỗ',
            'ơ','ờ','ớ','ợ','ở','ỡ'
        ],
        'o',
        $text
    );

    $text = str_replace(
        [
            'ù','ú','ụ','ủ','ũ',
            'ư','ừ','ứ','ự','ử','ữ'
        ],
        'u',
        $text
    );

    $text = str_replace(
        [
            'ỳ','ý','ỵ','ỷ','ỹ'
        ],
        'y',
        $text
    );

    $text = str_replace(
        ['đ'],
        'd',
        $text
    );


    /*
     * Loại bỏ ký tự đặc biệt
     */
    $text = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $text
    );


    /*
     * Xóa dấu - đầu cuối
     */
    $text = trim(
        $text,
        '-'
    );


    return $text;
}


// =====================================================
// XỬ LÝ FORM
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim(
        $_POST['name'] ?? ''
    );

    $uploadedImage = uploadManufacturerLogo(
        $_FILES['image'] ?? null,
        $error
    );

    if ($uploadedImage !== null) {
        $image = $uploadedImage;
    }


    // =================================================
    // KIỂM TRA TÊN
    // =================================================

    if ($error !== '') {

        // Lỗi upload đã được gán trong helper.

    } elseif ($name === '') {

        $error =
            'Vui lòng nhập tên hãng sản xuất.';

    } else {


        // =============================================
        // TẠO SLUG
        // =============================================

        $newSlug =
            createSlug($name);


        if ($newSlug === '') {

            $error =
                'Không thể tạo slug từ tên hãng.';

        } else {


            // =========================================
            // KIỂM TRA SLUG TRÙNG
            // =========================================

            $sqlCheck = "
                SELECT id
                FROM manufacturers
                WHERE slug = :slug
                AND id != :id
                LIMIT 1
            ";

            $stmtCheck =
                $pdo->prepare(
                    $sqlCheck
                );

            $stmtCheck->execute([
                ':slug' => $newSlug,
                ':id'   => $id
            ]);


            if ($stmtCheck->fetch()) {

                $error =
                    'Slug này đã tồn tại. '
                    . 'Vui lòng đổi tên hãng.';

            } else {


                // =====================================
                // CẬP NHẬT DATABASE
                // =====================================

                try {

                    $pdo->beginTransaction();
                    $sqlUpdate = "
                        UPDATE manufacturers

                        SET
                            name = :name,
                            slug = :slug,
                            image = :image

                        WHERE id = :id
                    ";

                    $stmtUpdate =
                        $pdo->prepare(
                            $sqlUpdate
                        );


                    $stmtUpdate->execute([

                        ':name' =>
                            $name,

                        ':slug' =>
                            $newSlug,

                        ':image' =>
                            $image,

                        ':id' =>
                            $id

                    ]);


                    // =================================
                    // THÀNH CÔNG
                    // =================================

                    $groupModel->sync($id, $selectedGroups);
                    $pdo->commit();
                    header(
                        'Location: manufacturers.php'
                    );

                    exit;


                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();

                    $error =
                        'Không thể cập nhật hãng sản xuất.';
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
        Sửa hãng sản xuất
    </title>


    <link rel="stylesheet" href="../assets/css/manufacturers/manufacturers-edit.css">

<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/manufacturers/groups.css">
</head>


<body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>


<div class="page">


    <!-- =========================================
         HEADER
    ========================================== -->

    <div class="page-header">

        <h1>
            Sửa hãng sản xuất
        </h1>


        <a
            href="manufacturers.php"
            class="btn btn-back"
        >
            <?= adminIcon('back') ?> Danh sách hãng
        </a>

    </div>


    <!-- =========================================
         FORM
    ========================================== -->

    <div class="form-box">


        <?php if ($error !== ''): ?>

            <div class="error">

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- TÊN HÃNG -->

            <div class="form-group">

                <label>
                    Hãng sản xuất
                    <span style="color:#ef4444;">
                        *
                    </span>
                </label>


                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Ví dụ: Mitsubishi"
                    required
                >


                <div class="help">

                    Ví dụ:
                    Mitsubishi,
                    Siemens,
                    Omron,
                    Panasonic,
                    Schneider...

                </div>

            </div>


            <!-- LOGO HÃƒNG -->

            <div class="form-group">

                <label for="image">
                    Logo hãng
                </label>

                <?php if (!empty($image)): ?>

                    <img
                        src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Logo <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                        style="display:block;max-width:160px;max-height:70px;object-fit:contain;margin:8px 0 12px;"
                    >

                <?php endif; ?>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div class="help">
                    Để trống nếu muốn giữ logo hiện tại. Chấp nhận JPG, PNG hoặc WebP; tối đa 2 MB.
                </div>

            </div>


            <!-- SLUG -->

            <div class="form-group">

                <label>
                    Slug
                </label>


                <input
                    type="text"
                    value="<?= htmlspecialchars(
                        $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    readonly
                >


                <div class="help">

                    Slug sẽ được hệ thống
                    tự động tạo lại từ tên hãng.

                </div>

            </div>


            <!-- FOOTER -->

            <?php require __DIR__ . '/views/group-fields.php'; ?>
            <div class="form-footer">


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
                    ✓ Lưu thay đổi
                </button>


            </div>


        </form>


    </div>


</div>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
