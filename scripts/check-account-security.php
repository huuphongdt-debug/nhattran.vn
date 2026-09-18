<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/AccountSecurity.php';
function accountExpect(bool $value): void { if (!$value) throw new RuntimeException('Account security assertion failed'); }
$pdo->beginTransaction();
try {
    $suffix=bin2hex(random_bytes(12));
    $old='Original-password-'.$suffix;
    $new='Replacement-password-'.$suffix;
    $stmt=$pdo->prepare("INSERT INTO users(username,email,password,role,status) VALUES (?,?,?,'manager','active')");
    $stmt->execute(['audit-'.$suffix,$suffix.'@example.invalid',password_hash($old,PASSWORD_DEFAULT)]);
    $id=(int)$pdo->lastInsertId();
    $security=new AccountSecurity($pdo);
    foreach([['wrong',$new],[$old,'short'],[$old,$old]] as [$current,$next]) {
        try {$security->changePassword($id,$current,$next);throw new RuntimeException('Invalid password accepted');}
        catch(InvalidArgumentException $expected){}
    }
    $security->changePassword($id,$old,$new);
    $hash=$pdo->query('SELECT password FROM users WHERE id='.$id)->fetchColumn();
    accountExpect(password_verify($new,$hash) && !password_verify($old,$hash));
    for($i=0;$i<5;$i++)accountExpect($security->allowAttempt($suffix,'test-'.$suffix));
    accountExpect(!$security->allowAttempt($suffix,'test-'.$suffix));
    accountExpect(!$security->allowAttempt($suffix,'other-'.$suffix));
    $pdo->prepare('UPDATE auth_throttle SET expires_at=DATE_SUB(NOW(),INTERVAL 1 MINUTE) WHERE bucket=?')->execute([hash('sha256','login:account:'.$suffix)]);
    accountExpect($security->allowAttempt($suffix,'test-'.$suffix));
    for($i=0;$i<30;$i++)accountExpect($security->allowAttempt('ip-user-'.$i.'-'.$suffix,'ip-limit-'.$suffix));
    accountExpect(!$security->allowAttempt('ip-user-final-'.$suffix,'ip-limit-'.$suffix));
    echo "Password verification/update, account/IP limits and expiry passed; fixtures rolled back.\n";
} finally {$pdo->rollBack();}
