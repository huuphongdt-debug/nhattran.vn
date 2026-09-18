<?php
/*
 * Trang liên hệ: tiêu đề, liên kết gọi/Zalo và partial contact-section.php.
 * Dùng siteStart/siteEnd để nạp header, footer và tài nguyên chung.
 */
 require_once __DIR__ . '/includes/site.php'; siteStart('Liên hệ', 'contact'); ?>
<main>
    <?php siteHero('Kết nối với Nhật Trần', 'Trao đổi nhu cầu về thiết bị, báo giá và hỗ trợ kỹ thuật cùng chúng tôi.', 'LẮNG NGHE · TƯ VẤN · ĐỒNG HÀNH', 'Sẵn sàng tiếp nhận yêu cầu', [companyValue($pdo, 'company_phone'), 'Zalo', 'Email'], ['Liên hệ' => null]); ?>
    <div class="container"><div class="page-actions"><a class="btn btn-primary" href="<?= siteEscape(companyLink($pdo, 'phone')) ?>">Gọi <?= siteEscape(companyValue($pdo, 'company_phone')) ?></a><a class="btn page-outline" href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>" target="_blank" rel="noopener noreferrer">Nhắn qua Zalo</a></div></div>
    <?php require __DIR__ . '/includes/contact-section.php'; ?>
</main>
<?php siteEnd(); ?>
