<?php
// $productReadiness do products-data.php chuẩn bị; dùng chung cho desktop/mobile.
$escapeReadiness = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<details class="product-readiness" id="product-readiness" <?= ($_GET['readiness'] ?? '') === '1' ? 'open' : '' ?>>
    <summary>Nội dung cần bổ sung: <?= count($productReadiness) ?> sản phẩm</summary>
    <p>Rà toàn bộ sản phẩm, bao gồm bản nháp. Kiểm tra này phát hiện dữ liệu thiếu và tệp ảnh không tồn tại; thông số kỹ thuật và ảnh thực tế vẫn cần đối chiếu trước khi công bố.</p>
    <?php if (!$productReadiness): ?>
        <p>Không phát hiện trường thông tin trống hoặc tệp ảnh bị thiếu.</p>
    <?php else: ?>
        <ul class="product-readiness-list">
            <?php foreach ($productReadiness as $item): ?>
                <li>
                    <div>
                        <strong><?= $escapeReadiness($item['name']) ?></strong>
                        <small>#<?= (int) $item['id'] ?> · <?= $item['status'] === 'active' ? 'Đang công khai' : 'Chưa công khai' ?></small>
                        <p><?= $escapeReadiness(implode(' · ', $item['issues'])) ?></p>
                    </div>
                    <a class="btn btn-edit" href="product-edit.php?id=<?= (int) $item['id'] ?>">Bổ sung</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</details>
