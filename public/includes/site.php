<?php
/*
 * Khung trang công khai: siteStart mở trang, siteHero nạp hero, siteEnd kết thúc trang.
 * Nạp helpers và cài đặt dùng chung; cần gọi các hàm theo cặp để giữ cấu trúc HTML.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/SettingsHelper.php';
require_once __DIR__ . '/helpers.php';

function siteStart(string $title, string $active): void
{
    ?>
    <!doctype html><html lang="vi"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= siteEscape($title) ?> | NHAT TRAN</title>

    <?php require __DIR__ . '/styles.php'; ?>
</head><body>
    <?php require __DIR__ . '/header.php';
}

function siteHero(
    string $title,
    string $intro,
    string $eyebrow,
    string $sideTitle,
    array $tags,
    array $crumbs,
    bool $detail = false
): void
{
    require __DIR__ . '/hero.php';
}

function siteEnd(): void
{
    global $pdo;
    require __DIR__ . '/footer.php';
    require __DIR__ . '/scripts.php';
    echo '</body></html>';
}
