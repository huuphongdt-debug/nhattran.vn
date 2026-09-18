<?php
/*
 * Trang quản lý dự án: projects-data.php xử lý dữ liệu trước khi xuất HTML.
 * views/index.php chọn form hoặc danh sách; shell chung cung cấp menu và icon.
 * projects.js xác nhận xóa từ các biểu mẫu có data-delete-project.
 */
 require __DIR__ . '/projects-data.php'; ?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quản lý dự án | NHAT TRAN</title>
<link rel="stylesheet" href="../assets/css/admin-common.css"><link rel="stylesheet" href="../assets/css/projects/projects.css">
<link rel="stylesheet" href="../assets/css/layout.css">
</head><body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<?php require __DIR__ . '/views/index.php'; ?>
<script src="../assets/js/projects/projects.js"></script>
<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body></html>
