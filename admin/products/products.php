<?php
/*
 * Trang danh sách sản phẩm: products-data.php chuẩn bị dữ liệu trước khi xuất HTML.
 * Khung quản trị dùng shell chung; nội dung danh sách nằm trong views/index.php.
 * Giữ thứ tự CSS admin-common, products và layout để không đổi giao diện.
 */
 require __DIR__ . '/products-data.php'; ?>
<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Quản lý sản phẩm | NHAT TRAN
    </title>


    <link rel="stylesheet" href="../assets/css/admin-common.css">

    <link rel="stylesheet" href="../assets/css/products/products.css">

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

<?php require __DIR__ . '/views/index.php'; ?>


<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>

</html>
