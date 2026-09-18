<?php
/*
 * Header và điều hướng dùng chung của website.
 * Class/data/ARIA của nút menu phối hợp với site.js và navigation.css; giữ đồng bộ khi bảo trì.
 */
 $active = $active ?? ''; ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php"><img src="../uploads/logo/LOGO%20NHATTRAN%202025.png" alt="NHAT TRAN — Trang chủ"></a>
        <button class="menu-toggle" type="button" aria-label="Mở menu" aria-expanded="false" aria-controls="site-navigation"><i class="fa-solid fa-bars"></i></button>
        <nav class="main-nav" id="site-navigation" aria-label="Điều hướng chính">
            <?php foreach (['index' => 'Trang chủ', 'about' => 'Giới thiệu', 'products' => 'Sản phẩm', 'services' => 'Dịch vụ', 'projects' => 'Dự án', 'news' => 'Tin tức', 'contact' => 'Liên hệ'] as $key => $label): ?>
                <a href="<?= $key ?>.php" <?= $active === $key ? 'class="active" aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
            <form class="header-search" action="products.php" method="get" role="search">
                <?php if ($active === 'products' && !empty($selectedCategoryId)): ?><input type="hidden" name="category" value="<?= (int) $selectedCategoryId ?>"><?php endif; ?>
                <label class="sr-only" for="header-product-search">Tìm sản phẩm</label>
                <input id="header-product-search" name="keyword" type="search" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars((string) ($_GET['keyword'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" aria-label="Tìm sản phẩm"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </nav>
    </div>
</header>
