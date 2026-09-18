<section class="dash-panel">
    <div class="dash-panel-heading">
        <h2>Thao tác nhanh</h2><span>Bắt đầu từ đây</span>
    </div>
    <div class="dash-actions">
<?php
// Thao tác nhanh dùng cấu hình từ data.php; không thực hiện ghi dữ liệu khi tải dashboard.
foreach ($dashboardActions as [$label, $url, $description, $icon]): ?>
        <a href="<?= adminUrl($url) ?>"><span class="dash-action-icon" aria-hidden="true"><?= adminIcon($icon) ?></span><span><strong><?= $label ?></strong><small><?= $description ?></small></span><span><?= adminIcon('external') ?></span></a><?php endforeach; ?>
    </div>
</section>
