<?php
/*
 * Kết thúc phiên đăng nhập: mở phiên hiện tại, xóa dữ liệu rồi hủy phiên phía máy chủ.
 * Sau đó chuyển về login.php và exit; không xuất HTML trước header().
 */


require_once __DIR__ . '/../app/Helpers/SessionHelper.php'; startSiteSession();


/*
|--------------------------------------------------------------------------
| Xóa toàn bộ dữ liệu session
|--------------------------------------------------------------------------
*/

$_SESSION = [];
// Remove the browser cookie using the same attributes as the session cookie.
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $cookie['path'],
    'domain' => $cookie['domain'],
    'secure' => $cookie['secure'],
    'httponly' => $cookie['httponly'],
    'samesite' => $cookie['samesite'],
]);


/*
|--------------------------------------------------------------------------
| Hủy session
|--------------------------------------------------------------------------
*/

session_destroy();


/*
|--------------------------------------------------------------------------
| Quay về trang đăng nhập
|--------------------------------------------------------------------------
*/

header("Location: login.php");

exit;
