<?php
// Verify homepage wiring with temporary settings; always roll back.
require __DIR__ . '/../config/database.php';
$pdo->beginTransaction();
try {
    $values = ['company_phone' => '012 345 6789', 'company_zalo' => '0987654321', 'company_email' => 'audit@example.invalid', 'company_tax_id' => 'AUDIT-TAX', 'company_bank_account' => 'AUDIT-ACCOUNT', 'company_bank_name' => 'AUDIT-BANK'];
    $stmt = $pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    foreach ($values as $key => $value) $stmt->execute([$key,$value]);
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['SCRIPT_NAME'] = '/NHATTRAN/public/index.php';
    ob_start(); require __DIR__ . '/../public/index.php'; $html = ob_get_clean();
    foreach (['tel:0123456789','https://zalo.me/0987654321','mailto:audit@example.invalid','AUDIT-TAX','AUDIT-ACCOUNT','AUDIT-BANK'] as $expected) {
        if (!str_contains($html,$expected)) throw new RuntimeException('Missing configured contact: '.$expected);
    }
    if (str_contains($html,'tel:0905576690') || str_contains($html,'https://zalo.me/0905576690')) throw new RuntimeException('Hardcoded contact remains');
    echo "Configured homepage contacts and legal fields passed; fixtures rolled back.\n";
} finally { $pdo->rollBack(); }
