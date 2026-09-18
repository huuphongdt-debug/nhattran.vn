<section class="dash-panel">
    <div class="dash-panel-heading">
        <h2>Sản phẩm mới nhất</h2><a href="<?= adminUrl('products/products.php') ?>">Xem tất cả <?= adminIcon('arrow') ?></a>
    </div>
    <div class="dash-table">
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th><span class="dash-visually-hidden">Thao tác</span>
                    </th>
                </tr>
            </thead>
            <tbody>
<?php
// Model cung cấp danh sách sản phẩm mới nhất; partial chỉ hiển thị, không truy vấn thêm.
// Escape tên, SKU, danh mục và trạng thái trước khi đưa vào HTML.
foreach ($overview['latest'] as $product): ?><tr>
                <td><strong><?= adminEscape($product['name']) ?></strong><small><?= adminEscape($product['sku'] ?: 'Chưa có mã SKU') ?></small>
                </td>
                <td><?= adminEscape($product['category_name'] ?: 'Chưa phân loại') ?></td>
                <td><span class="dash-status <?= $product['status'] === 'active' ? 'is-active' : '' ?>"><?= adminEscape(
                    [
                    'active' => 'Hoạt động',
                    'draft' => 'Bản nháp',
                    'inactive' => 'Đã ẩn'
                    ][$product['status']] ?? $product['status']
                    ) ?></span>
                </td>
                <td><a href="<?= adminUrl('products/product-edit.php?id=' . (int) $product['id']) ?>">Sửa <?= adminIcon('arrow') ?></a>
                </td>
            </tr><?php endforeach; ?>
            <?php if (!$overview['latest']): ?><tr>
                <td colspan="4" class="dash-empty"><?= $dashboardError ? 'Chưa tải được dữ liệu.' : 'Chưa có sản phẩm. Thêm sản phẩm đầu tiên để bắt đầu.' ?></td>
            </tr><?php endif; ?>
        </tbody>
    </table>
</div>
</section>
