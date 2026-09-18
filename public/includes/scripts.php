<?php
/*
 * Ánh xạ tên trang sang JavaScript riêng; site.js luôn được nạp cho menu chung.
 * Khi thêm script trang mới, cập nhật $publicScripts và giữ đường dẫn tương đối từ public.
 */

// Chỉ tải JavaScript cần cho trang hiện tại.
$publicPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$publicScripts = [
    'index.php' => ['home.js'],
    'products.php' => ['products.js'],
    'product-detail.php' => ['pages.js'],
];
?>
<script src="js/site.js"></script>
<?php foreach ($publicScripts[$publicPage] ?? [] as $scriptFile): ?>
<script src="js/<?= htmlspecialchars($scriptFile, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
