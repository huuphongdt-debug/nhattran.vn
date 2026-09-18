<?php /*
 * Phần mở đầu trang dùng các tham số do siteHero() cung cấp.
 * Tiêu đề, nhãn, thẻ và breadcrumb được dựng trong template; giữ thứ tự tham số hàm gọi.
 */
?><section class="page-hero inner-hero<?= $detail ? ' inner-hero-detail' : '' ?>" aria-labelledby="page-title">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Đường dẫn">
                <a href="index.php">Trang chủ</a>
<?php foreach ($crumbs as $label => $url): ?>
                    <span aria-hidden="true">/</span>
                    <?php if ($url): ?><a href="<?= siteEscape($url) ?>"><?= siteEscape($label) ?></a>
                    <?php else: ?><span aria-current="page"><?= siteEscape($label) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <div class="inner-hero-layout">
                <div class="inner-hero-copy">
                    <span class="inner-hero-eyebrow"><?= siteEscape($eyebrow) ?></span>
                    <h1 id="page-title"><?= siteEscape($title) ?><span aria-hidden="true">.</span></h1>
                    <?php if ($intro !== ''): ?><p><?= siteEscape($intro) ?></p><?php endif; ?>
                </div>
                <div class="inner-hero-identity">
                    <p class="inner-hero-company"><?= siteEscape($sideTitle) ?></p>
                    <ul aria-label="Thông tin nổi bật">
                        <?php foreach ($tags as $tag): ?><li><?= siteEscape($tag) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>
