<?php
/*
 * Đọc cài đặt website theo cặp khóa–giá trị; cache tĩnh trong lần chạy PHP hiện tại.
 * Khi truy vấn lỗi, trả mảng rỗng để siteSetting() dùng giá trị mặc định do bên gọi cung cấp.
 */


function siteSettings(PDO $pdo): array
{
    static $settings;
    if ($settings !== null) {
        return $settings;
    }

    try {
        $settings = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Throwable $exception) {
        $settings = [];
    }

    return $settings;
}

function siteSetting(PDO $pdo, string $key, string $default = ''): string
{
    $settings = siteSettings($pdo);
    return (string) ($settings[$key] ?? $default);
}

function companyValue(PDO $pdo, string $key): string
{
    static $fields;
    $fields ??= require __DIR__ . '/../../config/company.php';
    return siteSetting($pdo, $key, $fields[$key][1] ?? '');
}

/** Only allow known contact URL schemes, even for old values already in the database. */
function companyLink(PDO $pdo, string $channel): string
{
    if ($channel === 'phone') return 'tel:' . preg_replace('/[^0-9+]/', '', companyValue($pdo, 'company_phone'));
    if ($channel === 'zalo') return 'https://zalo.me/' . preg_replace('/[^0-9]/', '', companyValue($pdo, 'company_zalo'));
    if ($channel === 'email') {
        $email = companyValue($pdo, 'company_email');
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $email : '';
    }
    $url = companyValue($pdo, 'company_' . $channel);
    return filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true) ? $url : '';
}
