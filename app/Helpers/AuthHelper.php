<?php
/*
 * Helper dùng trạng thái admin_user trong session để kiểm tra đăng nhập và vai trò.
 * Bên gọi cần khởi tạo phiên trước; các hàm require có thể chuyển hướng hoặc kết thúc yêu cầu.
 * requireManager() cho phép các vai trò quản lý theo canManageWebsite().
 */


/*
|--------------------------------------------------------------------------
| Auth Helper
|--------------------------------------------------------------------------
| Kiểm tra đăng nhập và phân quyền quản trị viên
|--------------------------------------------------------------------------
*/


/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['admin_user']);
}


/**
 * Lấy thông tin user đang đăng nhập
 */
function currentUser(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}


/**
 * Lấy role của user hiện tại
 */
function currentUserRole(): ?string
{
    return $_SESSION['admin_user']['role'] ?? null;
}


/**
 * Kiểm tra user có phải Admin không
 */
function isAdmin(): bool
{
    return currentUserRole() === 'admin';
}


/**
 * Kiểm tra user có phải Manager không
 */
function isManager(): bool
{
    return currentUserRole() === 'manager';
}


/**
 * Kiểm tra user có quyền quản trị website
 *
 * Admin và Manager đều được quản lý website.
 */
function canManageWebsite(): bool
{
    return in_array(
        currentUserRole(),
        ['admin', 'manager'],
        true
    );
}


/**
 * Bắt buộc phải đăng nhập
 *
 * Nếu chưa đăng nhập → quay về login.php
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {

        header("Location: /NHATTRAN/admin/login.php");

        exit;
    }
}


/**
 * Chỉ cho phép Admin
 *
 * Dùng cho các chức năng nhạy cảm:
 * - Quản lý quản trị viên
 * - Cài đặt hệ thống
 */
function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {

        http_response_code(403);

        die("Bạn không có quyền truy cập chức năng này.");
    }
}


/**
 * Cho phép Admin hoặc Manager
 */
function requireManager(): void
{
    requireLogin();

    if (!canManageWebsite()) {

        http_response_code(403);

        die("Bạn không có quyền truy cập chức năng này.");
    }
}