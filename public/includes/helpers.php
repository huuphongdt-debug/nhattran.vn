<?php
/*
 * Helper công khai: siteEscape cho HTML và siteAsset cho URL tài nguyên.
 * Không dùng siteEscape thay cho kiểm tra dữ liệu; không đổi quy tắc đường dẫn khi chỉ định dạng.
 */

function siteEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function siteAsset($path): string
{
    $path = trim((string) $path);
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '/')) return $path;
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $path)) return '';
    return str_starts_with($path, '../') ? $path : '../' . ltrim($path, '/');
}

