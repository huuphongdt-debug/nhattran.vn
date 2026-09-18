<?php /*
 * Thanh đầu trang dùng thông tin tài khoản từ layout.php.
 * Nút menu liên kết admin-sidebar qua aria-controls; layout.js đồng bộ trạng thái mở.
 * Tên và chữ viết tắt tài khoản được escape trước khi xuất HTML.
 */
?><header class="admin-topbar">
    <div class="admin-topbar-brand"><button class="admin-menu-button" type="button" aria-label="Đóng/mở menu quản trị" aria-controls="admin-sidebar" aria-expanded="true"><?= adminIcon('menu') ?></button><a href="<?= adminUrl('dashboard.php') ?>">NHAT TRAN<span>WORKSPACE</span></a></div>
    <div class="admin-topbar-tools"><a class="admin-website" href="<?= adminUrl('../public/index.php') ?>" target="_blank" rel="noopener">Xem website <?= adminIcon('external') ?></a><div class="admin-user-avatar" aria-hidden="true"><?= adminEscape(mb_strtoupper(mb_substr($adminName, 0, 1))) ?></div><div class="admin-user-label"><strong><?= adminEscape($adminName) ?></strong><small><?= $adminRoleLabel ?></small></div></div>
</header>
