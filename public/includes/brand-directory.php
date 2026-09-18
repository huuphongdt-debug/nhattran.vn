<?php
/*
 * Hiển thị $brandSections do ManufacturerGroup::directory() cung cấp.
 * Một nhóm có nhiều logo/tên hãng; hãng thiếu logo vẫn hiện tên, nhóm rỗng do model lọc.
 * Dùng helper của trang chủ để escape và tạo URL ảnh.
 */
 if ($brandSections): ?>
    <?php foreach ($brandSections as $sectionIndex => $brandSection): ?>
    <section class="brand-group" aria-labelledby="brand-group-<?= $sectionIndex ?>">
        <div class="brand-group-heading">
            <h3 id="brand-group-<?= $sectionIndex ?>"><?= homeEscape($brandSection['name']) ?></h3>
            <span><?= count($brandSection['brands']) ?> thương hiệu</span>
        </div>
        <div class="brand-grid">
            <?php foreach ($brandSection['brands'] as $brand): ?>
            <div class="brand-card">
                <?php if (!empty($brand['image'])): ?>
                <img src="<?= homeEscape(homeAssetUrl($brand['image'])) ?>" alt="Logo <?= homeEscape($brand['name']) ?>" width="155" height="62" loading="lazy" decoding="async">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-state">Thương hiệu phân phối sẽ sớm được cập nhật.</div>
<?php endif; ?>
