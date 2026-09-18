<?php
// CLI entry point and shared implementation for legacy bootstrap wrappers.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/Models/AccountSecurity.php';
$role = $bootstrapRole ?? ($argv[1] ?? '');
$username = trim((string) getenv('NHATTRAN_ACCOUNT_USERNAME'));
$email = trim((string) getenv('NHATTRAN_ACCOUNT_EMAIL'));
$password = (string) getenv('NHATTRAN_ACCOUNT_PASSWORD');
try {
    if (!in_array($role, ['admin','manager'], true) || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Set NHATTRAN_ACCOUNT_USERNAME, NHATTRAN_ACCOUNT_EMAIL, NHATTRAN_ACCOUNT_PASSWORD and choose admin or manager.');
    }
    AccountSecurity::validatePassword($password);
    require __DIR__ . '/../config/database.php';
    $stmt = $pdo->prepare("INSERT INTO users(username,email,password,full_name,role,status) VALUES (?,?,?,?,?,'active')");
    $stmt->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT),$username,$role]);
    echo "Account created. Password is not displayed.\n";
} catch (InvalidArgumentException $e) { fwrite(STDERR,$e->getMessage()."\n"); exit(1); }
catch (Throwable $e) { fwrite(STDERR,"Account not created. Check database access and duplicate username/email.\n"); exit(1); }
