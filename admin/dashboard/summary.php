<section class="dash-stats" aria-label="Thống kê nội dung">
<?php
// Ghép các thẻ chính với danh mục, hãng và banner; số lượng lấy từ overview.counts.
// Dấu — phân biệt lỗi tải dữ liệu với số lượng thực tế bằng 0.
foreach (array_merge(
    $dashboardCards,
    [
        ['product_categories', 'Danh mục', 'categories/categories.php', 'category'],
        ['manufacturers', 'Hãng sản xuất', 'manufacturers/manufacturers.php', 'building'],
        ['banners', 'Banner', 'banners/banners.php', 'image']
    ]
) as [$key, $label, $url, $icon]): ?>
    <a class="dash-stat" href="<?= adminUrl($url) ?>"><span class="dash-stat-top"><?= $label ?><span aria-hidden="true"><?= adminIcon($icon) ?></span></span><strong><?= $dashboardError ? '—' : (int) $overview['counts'][$key] ?></strong><span class="dash-stat-bottom">Quản lý <?= mb_strtolower($label) ?> <span><?= adminIcon('external') ?></span></span></a>
    <?php endforeach; ?>
    <a class="dash-stat" href="<?= adminUrl('products/products.php?readiness=1#product-readiness') ?>">
        <span class="dash-stat-top">Nội dung cần bổ sung<span aria-hidden="true"><?= adminIcon('edit') ?></span></span>
        <strong><?= $readinessCount === null ? '—' : (int) $readinessCount ?></strong>
        <span class="dash-stat-bottom"><?= $readinessCount === null ? 'Chưa tải được kết quả kiểm tra' : 'Xem sản phẩm cần bổ sung' ?> <span><?= adminIcon('external') ?></span></span>
    </a>
</section>
