<?php /*
 * Form thêm/sửa dùng chung $form, $token và projectEscape() từ projects-data.php.
 * Gửi POST action=save, kèm CSRF; multipart/form-data dùng cho ảnh upload.
 * status 0 là bản nháp, 1 là công bố; featured đánh dấu ưu tiên hiển thị.
 */
?><form class="project-panel project-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= projectEscape($token) ?>"><input type="hidden" name="action" value="save">
        <label>Tên dự án *<input name="name" value="<?= projectEscape($form['name']) ?>" required maxlength="255"></label>
        <div class="project-columns"><label>Khách hàng<input name="customer" value="<?= projectEscape($form['customer']) ?>" maxlength="255"></label><label>Địa điểm<input name="location" value="<?= projectEscape($form['location']) ?>" maxlength="255"></label></div>
        <label>Mô tả ngắn<textarea name="short_description" rows="3"><?= projectEscape($form['short_description']) ?></textarea></label>
        <label>Nội dung chi tiết<textarea name="description" rows="10"><?= projectEscape($form['description']) ?></textarea></label>
        <label>Ảnh đại diện<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><small>JPG, PNG, WebP tối đa 8 MB. Ảnh được tối ưu thành WebP để website tải nhanh.</small>
        <?php if ($form['main_image']): ?><img class="project-preview" src="../../<?= projectEscape($form['main_image']) ?>" alt="Ảnh hiện tại"><label class="project-check"><input type="checkbox" name="remove_image"> Bỏ ảnh hiện tại</label><?php endif; ?>
        <div class="project-columns"><label>Trạng thái<select name="status"><option value="0" <?= !$form['status'] ? 'selected' : '' ?>>Bản nháp — không hiển thị</option><option value="1" <?= $form['status'] ? 'selected' : '' ?>>Công bố trên website</option></select></label><label>Thứ tự hiển thị<input type="number" name="sort_order" min="0" max="999999" value="<?= (int) $form['sort_order'] ?>"></label></div>
        <label class="project-check"><input type="checkbox" name="featured" <?= $form['featured'] ? 'checked' : '' ?>> Dự án nổi bật (ưu tiên hiển thị trước)</label>
        <div class="project-nav"><button class="project-button" type="submit">Lưu dự án</button><a href="projects.php">Hủy</a></div>
    </form>
