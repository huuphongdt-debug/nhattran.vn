<?php
/** Shared session token for administrative forms. Call after session_start(). */
function adminCsrfToken(): string
{
    $_SESSION['admin_csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['admin_csrf'];
}

function requireAdminPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        exit('Thao tác này chỉ chấp nhận biểu mẫu POST.');
    }
    $token = $_POST['csrf'] ?? null;
    if (!is_string($token) || !hash_equals(adminCsrfToken(), $token)) {
        http_response_code(403);
        exit('Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.');
    }
}

function adminCsrfField(): string
{
    return '<input type="hidden" name="csrf" value="'
        . htmlspecialchars(adminCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/** Render an action as a real POST form, including query parameters as hidden fields. */
function adminPostAction(string $url, string $label, string $class, bool $confirm = false): string
{
    $escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $parts = parse_url(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
    parse_str($parts['query'] ?? '', $fields);
    $html = '<form class="admin-post-action" method="post" action="' . $escape($parts['path'] ?? '') . '"';
    if ($confirm) $html .= ' onsubmit="return confirm(\'Bạn có chắc muốn xóa mục này?\');"';
    $html .= '>' . adminCsrfField();
    foreach ($fields as $name => $value) {
        if (is_scalar($value)) $html .= '<input type="hidden" name="' . $escape($name) . '" value="' . $escape($value) . '">';
    }
    return $html . '<button type="submit" class="' . $escape($class) . '">' . $label . '</button></form>';
}
