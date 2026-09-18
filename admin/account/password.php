<?php
require_once __DIR__ . '/../includes/layout.php';
requireManager();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/AccountSecurity.php';
$error = '';
$notice = $_SESSION['password_changed'] ?? false;
unset($_SESSION['password_changed']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $security = new AccountSecurity($pdo);
        if (!$security->allowAttempt((string) $adminUser['id'], $_SERVER['REMOTE_ADDR'] ?? '', 'password')) {
            http_response_code(429);
            throw new InvalidArgumentException('Quá nhiều lần thử. Vui lòng chờ 15 phút.');
        }
        $current = is_string($_POST['current'] ?? null) ? $_POST['current'] : '';
        $next = is_string($_POST['next'] ?? null) ? $_POST['next'] : '';
        if ($next !== ($_POST['confirm'] ?? null)) throw new InvalidArgumentException('Mật khẩu xác nhận chưa khớp.');
        $security->changePassword((int) $adminUser['id'], $current, $next);
        session_regenerate_id(true);
        unset($_SESSION['admin_user']['password'], $_SESSION['admin_csrf']);
        $_SESSION['password_changed'] = true;
        header('Location: password.php'); exit;
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
    catch (Throwable $e) { error_log('Password change failed: '.$e->getMessage()); $error = 'Chưa đổi được mật khẩu. Vui lòng thử lại sau.'; }
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Đổi mật khẩu | NHAT TRAN</title><link rel="stylesheet" href="../assets/css/layout.css"></head><body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main><h1>Đổi mật khẩu</h1>
<?php if ($notice): ?><p role="status">Đã đổi mật khẩu thành công.</p><?php endif; ?>
<?php if ($error): ?><p role="alert"><?= adminEscape($error) ?></p><?php endif; ?>
<form method="post" class="card">
<?= adminCsrfField() ?>
<p>Ít nhất 12 ký tự, tối đa 72 byte. Nên dùng cụm mật khẩu riêng cho tài khoản này.</p>
<p><label>Mật khẩu hiện tại<br><input type="password" name="current" autocomplete="current-password" required></label></p>
<p><label>Mật khẩu mới<br><input type="password" name="next" autocomplete="new-password" minlength="12" required></label></p>
<p><label>Nhập lại mật khẩu mới<br><input type="password" name="confirm" autocomplete="new-password" minlength="12" required></label></p>
<button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
</form></main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?></body></html>
