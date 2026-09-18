<?php /*
 * Phần chọn nhiều nhóm dùng chung trong form thêm/sửa hãng.
 * Cần $manufacturerGroups, $selectedGroups và token phiên từ group-bootstrap.php.
 * groups[] gửi danh sách ID; groups_csrf gửi token kiểm tra biểu mẫu.
 */
?><fieldset class="manufacturer-group-fields">
    <legend>Danh mục hãng sản xuất</legend>
    <input type="hidden" name="groups_csrf" value="<?= adminEscape($_SESSION['manufacturer_groups_csrf']) ?>">
    <p>Một hãng có thể thuộc nhiều danh mục.</p>
    <div class="manufacturer-group-options">
    <?php foreach ($manufacturerGroups as $group): ?><label><input type="checkbox" name="groups[]" value="<?= (int) $group['id'] ?>" <?= in_array((int) $group['id'], $selectedGroups, true) ? 'checked' : '' ?>> <?= adminEscape($group['name']) ?></label><?php endforeach; ?>
    </div>
    <a href="groups.php" target="_blank" rel="noopener">Quản lý danh mục hãng ↗</a>
</fieldset>
