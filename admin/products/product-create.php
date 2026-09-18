<?php
/*
 * Thêm sản phẩm: tải lựa chọn danh mục/hãng, kiểm tra dữ liệu POST và xử lý ảnh.
 * Dữ liệu sản phẩm được lưu trong transaction; nhánh lỗi rollback và dọn file theo luồng hiện có.
 * Schema giá được chuẩn bị lúc triển khai; product-create.js hỗ trợ xem trước ảnh.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/Models/Product.php";
require_once __DIR__ . "/../../app/Controllers/ProductController.php";

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
| LẤY DANH MỤC SẢN PHẨM
|--------------------------------------------------------------------------
*/

$sqlCategories = "
    SELECT
        id,
        name
    FROM product_categories
    ORDER BY
        sort_order ASC,
        id ASC
";

$stmtCategories = $pdo->prepare($sqlCategories);

$stmtCategories->execute();

$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| LẤY DANH SÁCH HÃNG SẢN XUẤT
|--------------------------------------------------------------------------
*/

$sqlManufacturers = "
    SELECT
        id,
        name
    FROM manufacturers
    ORDER BY
        name ASC,
        id ASC
";

$stmtManufacturers = $pdo->prepare(
    $sqlManufacturers
);

$stmtManufacturers->execute();

$manufacturers =
    $stmtManufacturers->fetchAll(
        PDO::FETCH_ASSOC
    );

/*
|--------------------------------------------------------------------------
| BIẾN FORM
|--------------------------------------------------------------------------
*/

$name = '';
$sku = '';
$price = '';
$show_price = 0;
$manufacturer_id = '';
$category_id = '';
$status = 'draft';
$is_featured = 0;
$description = '';
$content = '';

/*
|--------------------------------------------------------------------------
| TỰ ĐỘNG XÁC ĐỊNH THỨ TỰ HIỂN THỊ TIẾP THEO
|--------------------------------------------------------------------------
*/

$stmtSortOrder = $pdo->query("
    SELECT COALESCE(MAX(sort_order), 0) + 1
    FROM products
");

$sort_order = (int) $stmtSortOrder->fetchColumn();

$error = '';

/*
|--------------------------------------------------------------------------
| XỬ LÝ SUBMIT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |----------------------------------------------------------------------
    | LẤY DỮ LIỆU FORM
    |----------------------------------------------------------------------
    */

    $name = trim($_POST['name'] ?? '');

    $sku = trim($_POST['sku'] ?? '');

    $price = trim($_POST['price'] ?? '');
    $show_price = isset($_POST['show_price']) ? 1 : 0;

    $manufacturer_id = $_POST['manufacturer_id'] ?? '';

    $category_id = $_POST['category_id'] ?? '';

    $status = $_POST['status'] ?? 'draft';

    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $description = trim($_POST['description'] ?? '');

    $content = trim($_POST['content'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA DỮ LIỆU
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $error = 'Vui lòng nhập tên sản phẩm.';

    } elseif (!is_numeric($sort_order)) {

        $error = 'Thứ tự hiển thị không hợp lệ.';

    } elseif (!in_array(
        $status,
        ['draft', 'active', 'inactive'],
        true
    )) {

        $error = 'Trạng thái sản phẩm không hợp lệ.';
    }


    /*
    |----------------------------------------------------------------------
    | KIỂM TRA SKU TRÙNG
    |----------------------------------------------------------------------
    */

    if ($error === '' && $sku !== '') {

        $sqlCheckSku = "
            SELECT id
            FROM products
            WHERE sku = :sku
            LIMIT 1
        ";

        $stmtCheckSku = $pdo->prepare($sqlCheckSku);

        $stmtCheckSku->execute([
            ':sku' => $sku
        ]);

        if ($stmtCheckSku->fetch()) {

            $error = 'SKU này đã tồn tại. Vui lòng nhập SKU khác.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA ẢNH
    |--------------------------------------------------------------------------
    */

    $uploadedImages = [];

    if (
        $error === '' &&
        isset($_FILES['images'])
    ) {

        $files = $_FILES['images'];

        $fileCount = count(
            array_filter(
                $files['name'],
                function ($name) {
                    return $name !== '';
                }
            )
        );


        /*
        | Không quá 5 ảnh
        */

        if ($fileCount > 5) {

            $error =
                'Bạn chỉ được tải tối đa 5 ảnh sản phẩm.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LƯU SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            /*
            |--------------------------------------------------------------------------
            | BẮT ĐẦU TRANSACTION
            |--------------------------------------------------------------------------
            */

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | TẠO SLUG
            |--------------------------------------------------------------------------
            */

            $slug = strtolower($name);

            $slug = preg_replace(
                '/[^\p{L}\p{N}\s-]/u',
                '',
                $slug
            );

            $slug = preg_replace(
                '/[\s-]+/u',
                '-',
                $slug
            );

            $slug = trim($slug, '-');


            /*
            |--------------------------------------------------------------------------
            | ĐẢM BẢO SLUG KHÔNG TRỐNG
            |--------------------------------------------------------------------------
            */

            if ($slug === '') {

                $slug = 'san-pham';
            }


            /*
            |--------------------------------------------------------------------------
            | KIỂM TRA SLUG TRÙNG
            |--------------------------------------------------------------------------
            */

            $baseSlug = $slug;

            $counter = 1;

            while (true) {

                $stmtSlug = $pdo->prepare("
                    SELECT id
                    FROM products
                    WHERE slug = :slug
                    LIMIT 1
                ");

                $stmtSlug->execute([
                    ':slug' => $slug
                ]);

                if (!$stmtSlug->fetch()) {

                    break;
                }

                $counter++;

                $slug = $baseSlug . '-' . $counter;
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT PRODUCT
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO products (
                    category_id,
                    manufacturer_id,
                    name,
                    slug,
                    sku,
                    price,
                    show_price,
                    description,
                    content,
                    status,
                    is_featured,
                    sort_order
                )

                VALUES (
                    :category_id,
                    :manufacturer_id,
                    :name,
                    :slug,
                    :sku,
                    :price,
                    :show_price,
                    :description,
                    :content,
                    :status,
                    :is_featured,
                    :sort_order
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                ':category_id' =>
                    $category_id !== ''
                        ? (int) $category_id
                        : null,

                ':name' => $name,

                ':slug' => $slug,

                ':sku' =>
                    $sku !== ''
                        ? $sku
                        : null,

                ':price' => $price !== '' ? max(0, (float) $price) : null,
                ':show_price' => $show_price,
                    
                ':manufacturer_id' =>
                    $manufacturer_id !== ''
                        ? (int) $manufacturer_id
                        : null,

                ':description' =>
                    $description !== ''
                        ? $description
                        : null,

                ':content' =>
                    $content !== ''
                        ? $content
                        : null,

                ':status' => $status,

                ':is_featured' => $is_featured,

                ':sort_order' =>
                    (int) $sort_order

            ]);


            /*
            |--------------------------------------------------------------------------
            | ID SẢN PHẨM VỪA TẠO
            |--------------------------------------------------------------------------
            */

            $productId =
                (int) $pdo->lastInsertId();


            /*
            |--------------------------------------------------------------------------
            | THƯ MỤC UPLOAD
            |--------------------------------------------------------------------------
            */

            $uploadDir =
                __DIR__ .
                "/../../uploads/products/";


            if (!is_dir($uploadDir)) {

                mkdir(
                    $uploadDir,
                    0755,
                    true
                );
            }


            /*
            |--------------------------------------------------------------------------
            | UPLOAD ẢNH
            |--------------------------------------------------------------------------
            */

            $allowedTypes = [

                'image/jpeg' => 'jpg',

                'image/png' => 'png',

                'image/webp' => 'webp'
            ];


            if (
                isset($_FILES['images']) &&
                !empty($_FILES['images']['name'][0])
            ) {

                $files = $_FILES['images'];


                for (
                    $i = 0;
                    $i < count($files['name']);
                    $i++
                ) {


                    /*
                    |--------------------------------------------------------------
                    | BỎ QUA FILE RỖNG
                    |--------------------------------------------------------------
                    */

                    if (
                        $files['name'][$i] === ''
                    ) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------
                    | KIỂM TRA UPLOAD
                    |--------------------------------------------------------------
                    */

                    if (
                        $files['error'][$i]
                        !== UPLOAD_ERR_OK
                    ) {

                        throw new Exception(
                            'Có lỗi khi tải ảnh lên.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------
                    | KIỂM TRA MIME TYPE
                    |--------------------------------------------------------------
                    */

                    // Đồng nhất giới hạn với upload tại trang sửa sản phẩm.
                    if ($files['size'][$i] > 5 * 1024 * 1024) {
                        throw new Exception('Mỗi ảnh sản phẩm tối đa 5 MB.');
                    }

                    $tmpName =
                        $files['tmp_name'][$i];

                    $mimeType =
                        mime_content_type($tmpName);


                    if (
                        !isset(
                            $allowedTypes[$mimeType]
                        )
                    ) {

                        throw new Exception(
                            'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------
                    | TẠO TÊN FILE
                    |--------------------------------------------------------------
                    */

                    $extension =
                        $allowedTypes[$mimeType];

                    $fileName =
                        'product_' .
                        $productId .
                        '_' .
                        ($i + 1) .
                        '_' .
                        uniqid() .
                        '.' .
                        $extension;


                    $destination =
                        $uploadDir .
                        $fileName;


                    /*
                    |--------------------------------------------------------------
                    | DI CHUYỂN FILE
                    |--------------------------------------------------------------
                    */

                    if (
                        !move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        throw new Exception(
                            'Không thể lưu ảnh sản phẩm.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------
                    | ĐƯỜNG DẪN LƯU DATABASE
                    |--------------------------------------------------------------
                    */

                    $imagePath =
                        'uploads/products/' .
                        $fileName;


                    /*
                    |--------------------------------------------------------------
                    | ẢNH ĐẦU TIÊN = ẢNH CHÍNH
                    |--------------------------------------------------------------
                    */

                    if ($i === 0) {

                        $stmtMainImage =
                            $pdo->prepare("
                                UPDATE products
                                SET image = :image
                                WHERE id = :id
                            ");

                        $stmtMainImage->execute([

                            ':image' =>
                                $imagePath,

                            ':id' =>
                                $productId
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------
                    | LƯU VÀO product_images
                    |--------------------------------------------------------------
                    */

                    $stmtImage =
                        $pdo->prepare("
                            INSERT INTO product_images (
                                product_id,
                                image,
                                sort_order
                            )

                            VALUES (
                                :product_id,
                                :image,
                                :sort_order
                            )
                        ");

                    $stmtImage->execute([

                        ':product_id' =>
                            $productId,

                        ':image' =>
                            $imagePath,

                        ':sort_order' =>
                            $i + 1
                    ]);


                    /*
                    | Lưu danh sách file để rollback nếu cần
                    */

                    $uploadedImages[] =
                        $destination;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | HOÀN TẤT
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | CHUYỂN VỀ DANH SÁCH
            |--------------------------------------------------------------------------
            */

            header(
                "Location: products.php?message="
                . urlencode(
                    "Thêm sản phẩm thành công."
                )
            );

            exit;


        } catch (
            Throwable $e
        ) {


            /*
            |--------------------------------------------------------------------------
            | ROLLBACK DATABASE
            |--------------------------------------------------------------------------
            */

            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | XÓA FILE ĐÃ UPLOAD
            |--------------------------------------------------------------------------
            */

            foreach (
                $uploadedImages as $file
            ) {

                if (
                    file_exists($file)
                ) {

                    unlink($file);
                }
            }


            $error =
                "Không thể thêm sản phẩm. "
                . $e->getMessage();
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

    <title>Thêm sản phẩm | NHAT TRAN</title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/products/product-create.css?v=2">

<link rel="stylesheet" href="../assets/css/layout.css">
</head>


<body class="admin-shell">


<!-- =========================
     HEADER
========================= -->

<?php require __DIR__ . '/../includes/shell-start.php'; ?>



<!-- =========================
     MAIN
========================= -->

<main class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">


        <h1>

            Thêm sản phẩm

        </h1>


        <a
            href="products.php"
            class="btn btn-back"
        >

            <?= adminIcon('back') ?> Danh sách sản phẩm

        </a>


    </div>



    <!-- FORM -->

    <div class="form-box">


        <?php if ($error !== ''): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <form
            method="POST"
            action=""
            enctype="multipart/form-data"
        >
            <?php echo adminCsrfField(); ?>


            <!-- TÊN + SKU -->

            <div class="form-row">


                <div class="form-group">

                    <label for="name">

                        Tên sản phẩm
                        <span class="required">*</span>

                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        placeholder="Nhập tên sản phẩm"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="sku">

                        SKU

                    </label>


                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        value="<?= htmlspecialchars($sku) ?>"
                        placeholder="Ví dụ: SP-001"
                    >


                    <div class="help">

                        Mã sản phẩm duy nhất.

                    </div>

                </div>

                <div class="form-group">

                    <label for="manufacturer_id">
                        Hãng sản xuất
                    </label>

                    <select
                        id="manufacturer_id"
                        name="manufacturer_id"
                        >

                        <option value="">
                            -- Chọn hãng sản xuất --
                        </option>

                        <?php foreach (
                            $manufacturers
                            as $manufacturer
                        ): ?>

                        <option
                            value="<?= (int) $manufacturer['id'] ?>"
                            <?= (
                                (string) $manufacturer_id
                                ===
                                (string) $manufacturer['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $manufacturer['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                        <?php endforeach; ?>

                    </select>

                    <div class="help">
                        Chọn hãng sản xuất từ danh sách.
                    </div>

                </div>


            </div>



            <!-- DANH MỤC + TRẠNG THÁI -->

            <div class="form-row">


                <div class="form-group">

                    <label for="category_id">

                        Danh mục

                    </label>


                    <select
                        id="category_id"
                        name="category_id"
                    >

                        <option value="">

                            -- Chọn danh mục --

                        </option>


                        <?php foreach (
                            $categories
                            as $category
                        ): ?>


                            <option
                                value="<?= (int) $category['id'] ?>"
                                <?= (
                                    (string) $category_id
                                    ===
                                    (string) $category['id']
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $category['name']
                                ) ?>

                            </option>


                        <?php endforeach; ?>


                    </select>

                </div>

                <div class="form-group">
                    <label for="price">Giá sản phẩm</label>
                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" min="0" step="1000" placeholder="Ví dụ: 1250000">
                    <div class="help">Để trống nếu chưa có giá.</div>
                </div>

                <div class="form-group price-toggle">
                    <label><input type="checkbox" name="show_price" value="1" <?= $show_price ? 'checked' : '' ?>> Hiển thị giá trên website</label>
                </div>



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
                                : ''
                            ?>
                        >

                            Nháp

                        </option>


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


            </div>

            <div class="form-group featured-option">
                <label for="is_featured">
                    <input
                        type="checkbox"
                        id="is_featured"
                        name="is_featured"
                        value="1"
                        <?= $is_featured === 1 ? 'checked' : '' ?>
                    >
                    Đánh dấu là sản phẩm nổi bật
                </label>

                <div class="help">
                    Sản phẩm được chọn sẽ hiển thị tại mục “Sản phẩm nổi bật” ở trang chủ.
                </div>
            </div>

            <!-- THỨ TỰ -->

            <div class="form-group">

                <label for="sort_order">

                    Thứ tự hiển thị

                </label>


                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="<?= htmlspecialchars($sort_order) ?>"
                    min="0"
                    step="1"
                >


                <div class="help">

                    Số càng nhỏ sẽ được ưu tiên hiển thị trước.

                </div>

            </div>

            <!-- =========================
     ẢNH SẢN PHẨM
========================= -->

<div class="form-group">

    <label for="images">

        Ảnh sản phẩm

    </label>


    <input
        type="file"
        id="images"
        name="images[]"
        accept="image/jpeg,image/png,image/webp"
        multiple
    >


    <div class="help">

        Có thể chọn tối đa 5 ảnh.
        Định dạng: JPG, PNG, WEBP.

    </div>


    <!-- PREVIEW -->

    <div
        id="imagePreview"
        class="image-preview"
    ></div>

</div>


            <!-- MÔ TẢ -->

            <div class="form-group">

                <label for="description">

                    Mô tả sản phẩm

                </label>


                <textarea
                    id="description"
                    name="description"
                    placeholder="Nhập mô tả sản phẩm..."
                ><?= htmlspecialchars($description) ?></textarea>

            </div>

            <!-- =========================
     NỘI DUNG CHI TIẾT
========================= -->

<div class="form-group">

    <label for="content">

        Nội dung chi tiết

    </label>


    <textarea
        id="content"
        name="content"
        placeholder="Nhập nội dung chi tiết sản phẩm..."
    ><?= htmlspecialchars($content) ?></textarea>

</div>



            <!-- ACTION -->

            <div class="form-actions">


                <a
                    href="products.php"
                    class="btn btn-back"
                >

                    Hủy

                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <?= adminIcon('plus') ?> Lưu sản phẩm

                </button>


            </div>


        </form>


    </div>


</main>

<script src="../assets/js/products/product-create.js"></script>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
