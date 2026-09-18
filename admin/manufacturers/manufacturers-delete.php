<?php
/*
 * Xóa hãng theo ID trong POST sau khi kiểm tra quyền, CSRF và số sản phẩm đang sử dụng.
 * Nếu còn sản phẩm liên quan, xuất thông báo thay vì xóa hãng.
 * File có các nhánh HTML thông báo riêng; giữ nguyên thứ tự kiểm tra và chuyển hướng.
 */


require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
requireManager();
requireAdminPost();
require_once __DIR__ . '/../../config/database.php';


// =====================================================
// LẤY ID HÃNG CẦN XÓA
// =====================================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;


// =====================================================
// KIỂM TRA ID
// =====================================================

if ($id <= 0) {

    header(
        'Location: manufacturers.php'
    );

    exit;
}


// =====================================================
// KIỂM TRA HÃNG CÓ TỒN TẠI KHÔNG
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
// KIỂM TRA HÃNG CÓ SẢN PHẨM KHÔNG
// =====================================================

$sqlProduct = "
    SELECT COUNT(*) AS total
    FROM products
    WHERE manufacturer_id = :manufacturer_id
";

$stmtProduct = $pdo->prepare(
    $sqlProduct
);

$stmtProduct->execute([
    ':manufacturer_id' => $id
]);

$productCount = (int) (
    $stmtProduct->fetchColumn()
);


// =====================================================
// NẾU CÓ SẢN PHẨM -> KHÔNG CHO XÓA
// =====================================================

if ($productCount > 0) {

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
            Không thể xóa hãng sản xuất
        </title>


        <link rel="stylesheet" href="../assets/css/manufacturers/manufacturers-delete.css">

    </head>


    <body>


        <div class="container">

            <div class="icon">
                ⚠️
            </div>


            <h1>
                Không thể xóa hãng sản xuất
            </h1>


            <p>

                Hãng

                <span class="manufacturer-name">

                    <?= htmlspecialchars(
                        $manufacturer['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </span>

                đang được sử dụng bởi

                <span class="product-count">

                    <?= $productCount ?>

                    sản phẩm

                </span>.

                <br>

                Vui lòng chuyển hoặc xóa các sản phẩm
                thuộc hãng này trước khi xóa hãng.

            </p>


            <div class="buttons">


                <a
                    href="manufacturers.php"
                    class="btn btn-back"
                >
                    ← Danh sách hãng
                </a>


                <a
                    href="../products/products.php"
                    class="btn btn-products"
                >
                    Xem sản phẩm
                </a>


            </div>

        </div>


    </body>

    </html>

    <?php

    exit;
}


// =====================================================
// XÓA HÃNG
// =====================================================

try {

    $sqlDelete = "
        DELETE FROM manufacturers
        WHERE id = :id
    ";

    $stmtDelete = $pdo->prepare(
        $sqlDelete
    );


    $stmtDelete->execute([
        ':id' => $id
    ]);


    // =================================================
    // QUAY VỀ DANH SÁCH
    // =================================================

    header(
        'Location: manufacturers.php'
    );

    exit;


} catch (PDOException $e) {

    // =================================================
    // NẾU XẢY RA LỖI DATABASE
    // =================================================

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
            Lỗi xóa hãng
        </title>


        <link rel="stylesheet" href="../assets/css/manufacturers/manufacturers-delete-2.css">

    </head>


    <body>


        <div class="container">

            <h1>
                Không thể xóa hãng
            </h1>


            <p>

                Đã xảy ra lỗi khi xóa hãng sản xuất.

            </p>


            <a
                href="manufacturers.php"
                class="btn"
            >
                ← Quay lại danh sách
            </a>

        </div>


    </body>

    </html>

    <?php

    exit;
}
