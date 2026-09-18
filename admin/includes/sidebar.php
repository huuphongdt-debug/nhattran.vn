<?php /*
 * Menu dùng $adminNavigation và $adminSection do layout.php chuẩn bị.
 * Mục hiện tại có is-active và aria-current; adminUrl() tạo liên kết trong quản trị.
 */
?><aside class="admin-sidebar" id="admin-sidebar" aria-label="Menu quản trị">
    <p class="admin-nav-label">QUẢN LÝ WEBSITE</p>
    <nav>
        <?php foreach ($adminNavigation as [$key, $label, $url, $icon]): ?>
        <a href="<?= adminUrl($url) ?>" <?= $adminSection === $key ? 'class="is-active" aria-current="page"' : '' ?>><span class="admin-nav-icon" aria-hidden="true"><?= adminIcon($icon) ?></span><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-bottom"><p class="admin-nav-label">TÀI KHOẢN</p><a href="<?= adminUrl('logout.php') ?>">Đăng xuất <?= adminIcon('external') ?></a><small>NHAT TRAN · Quản trị nội dung</small></div>
</aside>
