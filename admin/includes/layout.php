<?php
/*
 * Khởi tạo khung quản trị: phiên đăng nhập, helper icon và thông tin tài khoản.
 * adminEscape() escape dữ liệu HTML; adminUrl() tạo URL theo vị trí thư mục /admin/.
 * $adminSection dùng đánh dấu mục menu hiện tại; navigation.php cung cấp cấu hình menu.
 */

require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();
requireLogin();
function adminEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function adminUrl(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/NHATTRAN/admin/dashboard.php');
    $position = strpos($script, '/admin/');
    return (
        $position === false
            ? '/NHATTRAN/admin/'
            : substr($script, 0, $position) . '/admin/'
    ) . ltrim($path, '/');
}
$adminSection = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'dashboard.php') $adminSection = 'dashboard';
$adminUser = currentUser();
$adminName = $adminUser['full_name'] ?? 'Quản trị viên';
$adminRoleLabel = isAdmin() ? 'Quản trị viên' : 'Quản lý nội dung';
$adminNavigation = require __DIR__ . '/navigation.php';
