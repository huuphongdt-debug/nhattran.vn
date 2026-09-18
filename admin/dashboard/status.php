<section class="dash-panel">
    <div class="dash-panel-heading">
        <h2>Nội dung cần xem lại</h2>
    </div>
    <div class="dash-review">
<?php
// Các nhóm nội dung cần xem lại dùng số liệu từ data.php; liên kết dẫn đến trang quản lý.
foreach ([
    ['Sản phẩm bản nháp', $overview['statuses']['draft'] ?? 0, 'products/products.php?status=draft'],
    ['Bài viết bản nháp', $overview['draftPosts'], 'posts/posts.php'],
    ['Dự án chưa công bố', $overview['draftProjects'], 'projects/projects.php']
] as [$label, $count, $url]): ?><a href="<?= adminUrl($url) ?>"><span><?= $label ?></span><strong><?= $dashboardError ? '—' : $count ?></strong><span><?= adminIcon('arrow') ?></span></a><?php endforeach; ?>
    </div>
    <p class="dash-note">Kiểm tra nội dung và hình ảnh trước khi công bố trên website.</p>
</section>
<section class="dash-panel dash-product-status">
    <div class="dash-panel-heading">
        <h2>Trạng thái sản phẩm</h2>
    </div>
<?php
// Ánh xạ mã trạng thái sản phẩm sang nhãn; thiếu một trạng thái thì số lượng bằng 0.
foreach ([
    'active' => 'Đang hoạt động',
    'draft' => 'Bản nháp',
    'inactive' => 'Đã ẩn'
] as $key => $label): ?><div><span><?= $label ?></span><strong><?= $dashboardError ? '—' : ($overview['statuses'][$key] ?? 0) ?></strong>
</div><?php endforeach; ?>
</section>
