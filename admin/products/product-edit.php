<?php
/*
 * Sửa sản phẩm theo ID; POST phân nhánh bằng action.
 * update_product: cập nhật thông tin; upload_images: thêm ảnh;
 * set_main_image: đặt ảnh chính; delete_image: xóa ảnh.
 * Các form và product-edit.js phải gửi đúng action/image_id tương ứng.
 * Schema giá được chuẩn bị lúc triển khai; phần HTML dùng dữ liệu đã tải cho biểu mẫu.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . "/../../app/Helpers/AuthHelper.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . '/../../app/Helpers/ProductImageHelper.php';
require_once __DIR__ . '/../../app/Helpers/UploadCleanupHelper.php';

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();



/*
|--------------------------------------------------------------------------
| LẤY ID SẢN PHẨM
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    header("Location: products.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| BIẾN THÔNG BÁO
|--------------------------------------------------------------------------
*/

$error = '';

$message = $_GET['message'] ?? '';


/*
|--------------------------------------------------------------------------
| LẤY SẢN PHẨM
|--------------------------------------------------------------------------
*/

$sqlProduct = "
    SELECT *
    FROM products
    WHERE id = :id
    LIMIT 1
";

$stmtProduct = $pdo->prepare($sqlProduct);

$stmtProduct->execute([
    ':id' => $id
]);

$product = $stmtProduct->fetch(PDO::FETCH_ASSOC);


if (!$product) {

    header("Location: products.php");

    exit;
}
$manufacturer_id =
    $product['manufacturer_id'] ?? '';
$price = $product['price'] ?? '';
$show_price = (int) ($product['show_price'] ?? 0);

/*
|--------------------------------------------------------------------------
| LẤY DANH MỤC
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

$manufacturers = $stmtManufacturers->fetchAll(
    PDO::FETCH_ASSOC
);

/*
|--------------------------------------------------------------------------
| LẤY HÌNH ẢNH SẢN PHẨM
|--------------------------------------------------------------------------
*/

$sqlImages = "
    SELECT
        id,
        image,
        sort_order,
        created_at
    FROM product_images
    WHERE product_id = :product_id
    ORDER BY
        sort_order ASC,
        id ASC
";

$stmtImages = $pdo->prepare($sqlImages);

$stmtImages->execute([
    ':product_id' => $id
]);

$productImages =
    $stmtImages->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| ĐẾM SỐ ẢNH
|--------------------------------------------------------------------------
*/

$imageCount = count($productImages);


/*
|--------------------------------------------------------------------------
| HÀM TẠO SLUG
|--------------------------------------------------------------------------
*/

function createSlug(string $text): string
{
    $text = trim($text);

    /*
    | Chuyển tiếng Việt
    */

    $text = mb_strtolower(
        $text,
        'UTF-8'
    );


    $replace = [

        'à' => 'a',
        'á' => 'a',
        'ạ' => 'a',
        'ả' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ầ' => 'a',
        'ấ' => 'a',
        'ậ' => 'a',
        'ẩ' => 'a',
        'ẫ' => 'a',
        'ă' => 'a',
        'ằ' => 'a',
        'ắ' => 'a',
        'ặ' => 'a',
        'ẳ' => 'a',
        'ẵ' => 'a',

        'è' => 'e',
        'é' => 'e',
        'ẹ' => 'e',
        'ẻ' => 'e',
        'ẽ' => 'e',
        'ê' => 'e',
        'ề' => 'e',
        'ế' => 'e',
        'ệ' => 'e',
        'ể' => 'e',
        'ễ' => 'e',

        'ì' => 'i',
        'í' => 'i',
        'ị' => 'i',
        'ỉ' => 'i',
        'ĩ' => 'i',

        'ò' => 'o',
        'ó' => 'o',
        'ọ' => 'o',
        'ỏ' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ồ' => 'o',
        'ố' => 'o',
        'ộ' => 'o',
        'ổ' => 'o',
        'ỗ' => 'o',
        'ơ' => 'o',
        'ờ' => 'o',
        'ớ' => 'o',
        'ợ' => 'o',
        'ở' => 'o',
        'ỡ' => 'o',

        'ù' => 'u',
        'ú' => 'u',
        'ụ' => 'u',
        'ủ' => 'u',
        'ũ' => 'u',
        'ư' => 'u',
        'ừ' => 'u',
        'ứ' => 'u',
        'ự' => 'u',
        'ử' => 'u',
        'ữ' => 'u',

        'ỳ' => 'y',
        'ý' => 'y',
        'ỵ' => 'y',
        'ỷ' => 'y',
        'ỹ' => 'y',

        'đ' => 'd'
    ];


    $text = strtr(
        $text,
        $replace
    );


    /*
    | Chỉ giữ chữ, số và dấu -
    */

    $text = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $text
    );


    /*
    | Xóa dấu - đầu cuối
    */

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

    $action = $_POST['action'] ?? 'update_product';

    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT THÔNG TIN SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    if ($action === 'update_product') {

        /*
        | Lấy dữ liệu
        */

        $name =
            trim(
                $_POST['name'] ?? ''
            );


        $sku =
            trim(
                $_POST['sku'] ?? ''
            );

        $price = trim($_POST['price'] ?? '');
        $show_price = isset($_POST['show_price']) ? 1 : 0;

        $manufacturer_id =
            $_POST['manufacturer_id'] ?? '';


        $category_id =
            $_POST['category_id'] ?? '';


        $status =
            $_POST['status'] ?? 'draft';

        $is_featured = isset($_POST['is_featured']) ? 1 : 0;


        $sort_order =
            $_POST['sort_order'] ?? 0;


        $description =
            trim(
                $_POST['description'] ?? ''
            );


        $content =
            trim(
                $_POST['content'] ?? ''
            );


        /*
        | Kiểm tra tên
        */

        if ($name === '') {

            $error =
                'Vui lòng nhập tên sản phẩm.';
        }


        /*
        | Kiểm tra sort order
        */

        elseif (!is_numeric($sort_order)) {

            $error =
                'Thứ tự hiển thị không hợp lệ.';
        }


        /*
        | Kiểm tra status
        */

        elseif (
            !in_array(
                $status,
                [
                    'draft',
                    'active',
                    'inactive'
                ],
                true
            )
        ) {

            $error =
                'Trạng thái sản phẩm không hợp lệ.';
        }


        /*
        | Kiểm tra SKU trùng
        */

        if (
            $error === ''
            && $sku !== ''
        ) {

            $sqlCheckSku = "
                SELECT id
                FROM products
                WHERE sku = :sku
                AND id != :id
                LIMIT 1
            ";

            $stmtCheckSku =
                $pdo->prepare(
                    $sqlCheckSku
                );


            $stmtCheckSku->execute([

                ':sku' => $sku,

                ':id' => $id

            ]);


            if ($stmtCheckSku->fetch()) {

                $error =
                    'SKU này đã tồn tại. '
                    . 'Vui lòng nhập SKU khác.';
            }
        }


        /*
        | Cập nhật
        */

        if ($error === '') {

            try {

                /*
                | Tạo slug
                */

                $baseSlug =
                    createSlug($name);


                /*
                | Kiểm tra slug trùng
                */

                $slug = $baseSlug;

                $counter = 1;


                while (true) {

                    $sqlCheckSlug = "
                        SELECT id
                        FROM products
                        WHERE slug = :slug
                        AND id != :id
                        LIMIT 1
                    ";

                    $stmtCheckSlug =
                        $pdo->prepare(
                            $sqlCheckSlug
                        );


                    $stmtCheckSlug->execute([

                        ':slug' => $slug,

                        ':id' => $id

                    ]);


                    if (
                        !$stmtCheckSlug->fetch()
                    ) {

                        break;
                    }


                    $counter++;


                    $slug =
                        $baseSlug
                        . '-'
                        . $counter;
                }


                /*
                | UPDATE
                */

                $sqlUpdate = "
                    UPDATE products

                    SET
                        name = :name,                       
                        slug = :slug,
                        sku = :sku,
                        price = :price,
                        show_price = :show_price,
                        manufacturer_id = :manufacturer_id,
                        category_id = :category_id,
                        description = :description,
                        content = :content,
                        status = :status,
                        is_featured = :is_featured,
                        sort_order = :sort_order,
                        updated_at = NOW()

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
                        $slug,

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

                    ':category_id' =>
                        $category_id !== ''
                            ? (int) $category_id
                            : null,

                    ':description' =>
                        $description !== ''
                            ? $description
                            : null,

                    ':content' =>
                        $content !== ''
                            ? $content
                            : null,

                    ':status' =>
                        $status,

                    ':is_featured' =>
                        $is_featured,

                    ':sort_order' =>
                        (int) $sort_order,

                    ':id' =>
                        $id

                ]);


                /*
                | Thành công
                */

                header(
                    "Location: product-edit.php?id="
                    . $id
                    . "&message="
                    . urlencode(
                        "Cập nhật sản phẩm thành công."
                    )
                );

                exit;


            } catch (PDOException $e) {

                $error =
                    "Không thể cập nhật sản phẩm. "
                    . $e->getMessage();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD HÌNH ẢNH
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'upload_images') {

        try {

                /*
        | Kiểm tra trạng thái sản phẩm
        */

        if (($product['status'] ?? '') !== 'active') {

            throw new Exception(
                'Chỉ được thêm hình ảnh khi sản phẩm đang ở trạng thái Hoạt động.'
            );
        }

            /*
            | Đếm ảnh hiện tại
            */

            $stmtCount =
                $pdo->prepare("
                    SELECT COUNT(*)
                    FROM product_images
                    WHERE product_id = :product_id
                ");


            $stmtCount->execute([
                ':product_id' => $id
            ]);


            $currentImageCount =
                (int) $stmtCount->fetchColumn();


            /*
            | Kiểm tra đã đủ 5 ảnh
            */

            if ($currentImageCount >= 5) {

                throw new Exception(
                    'Sản phẩm đã có đủ 5 ảnh.'
                );
            }


            /*
            | Kiểm tra file
            */

            if (
                !isset(
                    $_FILES['product_images']
                )
                || empty(
                    $_FILES['product_images']['name']
                )
            ) {

                throw new Exception(
                    'Vui lòng chọn ít nhất một ảnh.'
                );
            }


            $files =
                $_FILES['product_images'];


            $fileCount =
                count($files['name']);


            /*
            | Kiểm tra tổng số ảnh
            */

            if (
                $currentImageCount
                + $fileCount
                > 5
            ) {

                $remaining =
                    5
                    - $currentImageCount;


                throw new Exception(
                    "Chỉ có thể thêm "
                    . $remaining
                    . " ảnh nữa."
                );
            }


            /*
            | Thư mục upload
            */

            $uploadDir =
                __DIR__
                . '/../../uploads/products/';


            if (
                !is_dir($uploadDir)
            ) {

                if (
                    !mkdir(
                        $uploadDir,
                        0755,
                        true
                    )
                ) {

                    throw new Exception(
                        'Không thể tạo thư mục upload.'
                    );
                }
            }


            /*
            | Lấy sort_order cuối
            */

            $stmtSort =
                $pdo->prepare("
                    SELECT
                        COALESCE(
                            MAX(sort_order),
                            0
                        )
                    FROM product_images
                    WHERE product_id = :product_id
                ");


            $stmtSort->execute([
                ':product_id' => $id
            ]);


            $sortOrder =
                (int) $stmtSort->fetchColumn();


            /*
            | Loại ảnh cho phép
            */

            $allowedTypes = [

                'image/jpeg' =>
                    'jpg',

                'image/png' =>
                    'png',

                'image/webp' =>
                    'webp'

            ];


            /*
            | Kiểm tra MIME
            */

            $finfo =
                new finfo(
                    FILEINFO_MIME_TYPE
                );


            /*
            | Upload từng ảnh
            */

            for (
                $i = 0;
                $i < $fileCount;
                $i++
            ) {

                /*
                | Kiểm tra lỗi upload
                */

                if (
                    $files['error'][$i]
                    !== UPLOAD_ERR_OK
                ) {

                    throw new Exception(
                        'Có ảnh không thể upload.'
                    );
                }


                /*
                | Giới hạn 5MB
                */

                if (
                    $files['size'][$i]
                    > 5 * 1024 * 1024
                ) {

                    throw new Exception(
                        'Mỗi ảnh không được vượt quá 5MB.'
                    );
                }


                /*
                | Xác định MIME thật
                */

                $mimeType =
                    $finfo->file(
                        $files['tmp_name'][$i]
                    );


                if (
                    !isset(
                        $allowedTypes[$mimeType]
                    )
                ) {

                    throw new Exception(
                        'Chỉ cho phép ảnh JPG, PNG hoặc WEBP.'
                    );
                }


                $extension =
                    $allowedTypes[$mimeType];


                /*
                | Tăng sort order
                */

                $sortOrder++;


                /*
                | Tạo tên file
                */

                $fileName =
                    'product_'
                    . $id
                    . '_'
                    . $sortOrder
                    . '_'
                    . bin2hex(
                        random_bytes(8)
                    )
                    . '.'
                    . $extension;


                $targetPath =
                    $uploadDir
                    . $fileName;


                /*
                | Di chuyển file
                */

                if (
                    !move_uploaded_file(
                        $files['tmp_name'][$i],
                        $targetPath
                    )
                ) {

                    throw new Exception(
                        'Không thể lưu hình ảnh.'
                    );
                }


                /*
                | Đường dẫn Database
                */

                $imagePath =
                    'uploads/products/'
                    . $fileName;


                /*
                | Lưu Database
                */

                $stmtInsertImage =
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


                $stmtInsertImage->execute([

                    ':product_id' =>
                        $id,

                    ':image' =>
                        $imagePath,

                    ':sort_order' =>
                        $sortOrder

                ]);
            }


            /*
            | Thành công
            */

            syncProductCover($pdo, $id);

            header(
                "Location: product-edit.php?id="
                . $id
                . "&message="
                . urlencode(
                    "Thêm hình ảnh thành công."
                )
            );

            exit;


        } catch (Exception $e) {

            $error =
                'Không thể thêm hình ảnh. '
                . $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ĐẶT ẢNH CHÍNH
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'set_main_image') {

    $imageId = isset($_POST['image_id'])
        ? (int) $_POST['image_id']
        : 0;

    if ($imageId <= 0) {

        $error = 'Ảnh không hợp lệ.';

    } else {

        try {

            /*
            | Kiểm tra ảnh có thuộc sản phẩm này không
            */

            $stmtImage = $pdo->prepare("
                SELECT id
                FROM product_images
                WHERE id = :image_id
                AND product_id = :product_id
                LIMIT 1
            ");

            $stmtImage->execute([

                ':image_id' =>
                    $imageId,

                ':product_id' =>
                    $id

            ]);

            $image = $stmtImage->fetch(
                PDO::FETCH_ASSOC
            );


            if (!$image) {

                throw new Exception(
                    'Không tìm thấy hình ảnh.'
                );
            }


            /*
            | Lấy danh sách ảnh hiện tại
            */

            $stmtImagesOrder = $pdo->prepare("
                SELECT id
                FROM product_images
                WHERE product_id = :product_id
                ORDER BY sort_order ASC, id ASC
            ");

            $stmtImagesOrder->execute([
                ':product_id' => $id
            ]);

            $imagesOrder =
                $stmtImagesOrder->fetchAll(
                    PDO::FETCH_COLUMN
                );


            /*
            | Đưa ảnh được chọn lên đầu
            */

            $newOrder = [];

            $newOrder[] = $imageId;


            foreach ($imagesOrder as $currentImageId) {

                $currentImageId =
                    (int) $currentImageId;

                if (
                    $currentImageId !== $imageId
                ) {

                    $newOrder[] =
                        $currentImageId;
                }
            }


            /*
            | Cập nhật lại sort_order
            */

            $stmtUpdateOrder = $pdo->prepare("
                UPDATE product_images
                SET sort_order = :sort_order
                WHERE id = :id
                AND product_id = :product_id
            ");


            foreach (
                $newOrder
                as $index => $currentImageId
            ) {

                $stmtUpdateOrder->execute([

                    ':sort_order' =>
                        $index + 1,

                    ':id' =>
                        $currentImageId,

                    ':product_id' =>
                        $id

                ]);
            }


            syncProductCover($pdo, $id);

            header(
                "Location: product-edit.php?id="
                . $id
                . "&message="
                . urlencode(
                    "Đã đặt ảnh chính thành công."
                )
            );

            exit;


        } catch (Exception $e) {

            $error =
                'Không thể đặt ảnh chính. '
                . $e->getMessage();
        }
    }
    }

    /*
    |--------------------------------------------------------------------------
    | XÓA HÌNH ẢNH
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'delete_image') {

    $imageId = isset($_POST['image_id'])
        ? (int) $_POST['image_id']
        : 0;

    if ($imageId <= 0) {

        $error = 'Ảnh không hợp lệ.';

    } else {

        try {

            /*
            | Lấy thông tin ảnh
            */

            $stmtImage = $pdo->prepare("
                SELECT
                    id,
                    image,
                    sort_order
                FROM product_images
                WHERE id = :image_id
                AND product_id = :product_id
                LIMIT 1
            ");

            $stmtImage->execute([

                ':image_id' =>
                    $imageId,

                ':product_id' =>
                    $id

            ]);

            $image =
                $stmtImage->fetch(
                    PDO::FETCH_ASSOC
                );


            if (!$image) {

                throw new Exception(
                    'Không tìm thấy hình ảnh.'
                );
            }


            /*
            | Không cho xóa nếu chỉ còn 1 ảnh?
            |
            | Hiện tại cho phép xóa bình thường.
            */


            /*
            | Xóa database
            */

            $stmtDelete = $pdo->prepare("
                DELETE FROM product_images
                WHERE id = :image_id
                AND product_id = :product_id
            ");

            $stmtDelete->execute([

                ':image_id' =>
                    $imageId,

                ':product_id' =>
                    $id

            ]);


            /*
            | Xóa file vật lý
            */

            /*
            | Sắp xếp lại sort_order
            */

            $stmtRemaining = $pdo->prepare("
                SELECT id
                FROM product_images
                WHERE product_id = :product_id
                ORDER BY sort_order ASC, id ASC
            ");

            $stmtRemaining->execute([
                ':product_id' => $id
            ]);

            $remainingImages =
                $stmtRemaining->fetchAll(
                    PDO::FETCH_COLUMN
                );


            $stmtReorder = $pdo->prepare("
                UPDATE product_images
                SET sort_order = :sort_order
                WHERE id = :id
                AND product_id = :product_id
            ");


            foreach (
                $remainingImages
                as $index => $remainingImageId
            ) {

                $stmtReorder->execute([

                    ':sort_order' =>
                        $index + 1,

                    ':id' =>
                        (int) $remainingImageId,

                    ':product_id' =>
                        $id

                ]);
            }


            syncProductCover($pdo, $id);
            cleanupDeletedUpload($pdo, $image['image']);

            header(
                "Location: product-edit.php?id="
                . $id
                . "&message="
                . urlencode(
                    "Xóa hình ảnh thành công."
                )
            );

            exit;


        } catch (Exception $e) {

            $error =
                'Không thể xóa hình ảnh. '
                . $e->getMessage();
        }
    }
    }

}


/*
|--------------------------------------------------------------------------
| LẤY LẠI DỮ LIỆU SẢN PHẨM
|--------------------------------------------------------------------------
|
| Sau khi UPDATE, lấy lại dữ liệu mới nhất
|
*/

$sqlProduct = "
    SELECT *
    FROM products
    WHERE id = :id
    LIMIT 1
";

$stmtProduct =
    $pdo->prepare(
        $sqlProduct
    );

$stmtProduct->execute([
    ':id' => $id
]);

$product =
    $stmtProduct->fetch(
        PDO::FETCH_ASSOC
    );


/*
|--------------------------------------------------------------------------
| LẤY LẠI HÌNH ẢNH
|--------------------------------------------------------------------------
*/

$sqlImages = "
    SELECT
        id,
        image,
        sort_order,
        created_at
    FROM product_images
    WHERE product_id = :product_id
    ORDER BY
        sort_order ASC,
        id ASC
";

$stmtImages =
    $pdo->prepare(
        $sqlImages
    );

$stmtImages->execute([
    ':product_id' => $id
]);

$productImages =
    $stmtImages->fetchAll(
        PDO::FETCH_ASSOC
    );


$imageCount =
    count($productImages);

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
        Sửa sản phẩm |
        NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/products/product-edit.css?v=2">

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
    ================================================= -->

    <div class="page-header">

        <h1>

            Sửa sản phẩm

        </h1>


        <a
            href="products.php"
            class="btn btn-back"
        >

            <?= adminIcon('back') ?> Danh sách sản phẩm

        </a>

    </div>



    <!-- =================================================
         FORM SỬA THÔNG TIN
         
         FORM NÀY ĐỘC LẬP VỚI FORM UPLOAD ẢNH
    ================================================= -->

    <div class="form-box">


        <!-- THÔNG BÁO -->

        <?php if ($message !== ''): ?>

            <div class="success">

                <?= htmlspecialchars(
                    $message
                ) ?>

            </div>

        <?php endif; ?>


        <?php if ($error !== ''): ?>

            <div class="error">

                <?= htmlspecialchars(
                    $error
                ) ?>

            </div>

        <?php endif; ?>



        <!-- FORM UPDATE -->

        <form
            method="POST"
            action="product-edit.php?id=<?= $id ?>"
        >
            <?php echo adminCsrfField(); ?>


            <input
                type="hidden"
                name="action"
                value="update_product"
            >


            <!-- TÊN + SKU -->

            <div class="form-row">


                <div class="form-group">

                    <label for="name">

                        Tên sản phẩm

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars(
                            $product['name'] ?? ''
                        ) ?>"
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
                        value="<?= htmlspecialchars(
                            $product['sku'] ?? ''
                        ) ?>"
                        placeholder="Ví dụ: FX5U-32MT"
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
                        $manufacturers as $manufacturer
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

            <div class="form-group featured-option">
                <label for="is_featured">
                    <input
                        type="checkbox"
                        id="is_featured"
                        name="is_featured"
                        value="1"
                        <?= !empty($product['is_featured']) ? 'checked' : '' ?>
                    >
                    Đánh dấu là sản phẩm nổi bật
                </label>

                <div class="help">
                    Sản phẩm được chọn sẽ hiển thị tại mục “Sản phẩm nổi bật” ở trang chủ.
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
                                value="<?= (int)
                                    $category['id'] ?>"
                                <?= (
                                    (string)
                                    ($product['category_id'] ?? '')
                                    ===
                                    (string)
                                    $category['id']
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
                            <?= (
                                ($product['status'] ?? '')
                                === 'draft'
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            Nháp

                        </option>


                        <option
                            value="active"
                            <?= (
                                ($product['status'] ?? '')
                                === 'active'
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            Hoạt động

                        </option>


                        <option
                            value="inactive"
                            <?= (
                                ($product['status'] ?? '')
                                === 'inactive'
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            Tạm ẩn

                        </option>

                    </select>

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
                    value="<?= htmlspecialchars(
                        $product['sort_order'] ?? 0
                    ) ?>"
                    min="0"
                    step="1"
                >


                <div class="help">

                    Số càng nhỏ sẽ được ưu tiên
                    hiển thị trước.

                </div>

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
                ><?= htmlspecialchars(
                    $product['description'] ?? ''
                ) ?></textarea>

            </div>



            <!-- NỘI DUNG CHI TIẾT -->

            <div class="form-group">

                <label for="content">

                    Nội dung chi tiết

                </label>


                <textarea
                    id="content"
                    name="content"
                    class="content-textarea"
                    placeholder="Nhập nội dung chi tiết sản phẩm..."
                ><?= htmlspecialchars(
                    $product['content'] ?? ''
                ) ?></textarea>


                <div class="help">

                    Có thể nhập thông số kỹ thuật,
                    đặc điểm, ứng dụng và các
                    thông tin chi tiết khác.

                </div>

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

                    <?= adminIcon('check') ?> Lưu thay đổi

                </button>

            </div>


        </form>

        <!--
            QUAN TRỌNG:
            FORM SỬA SẢN PHẨM ĐÃ ĐÓNG Ở ĐÂY
        -->


    </div>



    <!-- =================================================
         HÌNH ẢNH SẢN PHẨM
         
         FORM UPLOAD ẢNH NẰM NGOÀI FORM TRÊN
    ================================================= -->

    <div class="image-section">


        <h2>

            Hình ảnh sản phẩm

        </h2>


        <div class="image-count">

            Đã có
            <strong>
                <?= $imageCount ?>
            </strong>
            / 5 ảnh.

        </div>



        <!-- =================================================
             DANH SÁCH ẢNH
        ================================================= -->

        <?php if (
            !empty($productImages)
        ): ?>

            <div class="image-list">


                <?php foreach (
                    $productImages
                    as $index => $image
                ): ?>


                    <div class="image-card">


                        <div class="image-card-header">

                            Ảnh
                            <?= $index + 1 ?>

                            <?php if (
                                $index === 0
                            ): ?>

                                <div class="main-image">

                                    Ảnh chính

                                </div>

                            <?php endif; ?>

                        </div>



                        <div class="image-card-body">

                            <img
                                src="../../<?= htmlspecialchars($image['image']) ?>"
                                alt="Ảnh sản phẩm <?= $index + 1 ?>"
                            >

                            <div class="image-order">

                                Thứ tự:
                                <?= (int) $image['sort_order'] ?>

                            </div>


                            <!-- =========================
                                THAO TÁC ẢNH
                            ========================= -->

                            <div class="image-actions">

                                <?php if ($index !== 0): ?>

                                    <button
                                        type="button"
                                        class="btn-main-image"
                                        onclick="setMainImage(<?= (int) $image['id'] ?>)"
                                    >
                                        <?= adminIcon('star') ?> Đặt làm ảnh chính
                                    </button>

                                <?php endif; ?>


                                <button
                                    type="button"
                                    class="btn-delete-image"
                                    onclick="deleteImage(<?= (int) $image['id'] ?>)"
                                >
                                    <?= adminIcon('trash') ?> Xóa ảnh
                                </button>

                            </div>

                        </div>


                    </div>


                <?php endforeach; ?>


            </div>

        <?php else: ?>

            <div class="help">

                Sản phẩm này chưa có hình ảnh.

            </div>

        <?php endif; ?>



        <!-- =================================================
             UPLOAD ẢNH
        ================================================= -->

        <?php if (
            $imageCount < 5
        ): ?>


            <div class="upload-box">


                <h3>

                    ➕ Thêm ảnh mới

                </h3>


                <div class="upload-info">

                    Bạn có thể chọn thêm tối đa

                    <strong>
                        <?= 5 - $imageCount ?>
                    </strong>

                    ảnh.

                    <br>

                    Định dạng:
                    JPG, PNG, WEBP.

                    Tối đa 5MB/ảnh.

                </div>



                <!--
                =================================================
                FORM UPLOAD

                FORM NÀY ĐỘC LẬP VỚI FORM SỬA SẢN PHẨM
                =================================================
                -->

                <form
                    method="POST"
                    action="product-edit.php?id=<?= $id ?>"
                    enctype="multipart/form-data"
                >
            <?php echo adminCsrfField(); ?>


                    <input
                        type="hidden"
                        name="action"
                        value="upload_images"
                    >


                    <input
                        type="file"
                        name="product_images[]"
                        class="upload-input"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        required
                    >


                    <br>


                    <button
                        type="submit"
                        class="btn btn-upload"
                    >

                        <?= adminIcon('image') ?> Thêm ảnh

                    </button>


                </form>

            </div>


        <?php else: ?>


            <div class="upload-box">

                <strong>

                    ✓ Sản phẩm đã có đủ 5 ảnh.

                </strong>

            </div>


        <?php endif; ?>


    </div>



</main>

<script src="../assets/js/products/product-edit.js" data-product-edit-url="product-edit.php?id=<?= (int) $id ?>"></script>

<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
