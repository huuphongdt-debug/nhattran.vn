<?php
/** Deployment-only migration for the schema previously changed during requests. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$mode = $argv[1] ?? '--check';
if (!in_array($mode, ['--check', '--apply'], true) || count($argv) > 2) {
    fwrite(STDERR, "Usage: php scripts/migrate-runtime-schema.php [--check|--apply]\n");
    exit(1);
}
require __DIR__ . '/../config/database.php';
$locked = false;
try {
    if ($mode === '--apply') {
        $lock = $pdo->query("SELECT GET_LOCK(CONCAT(DATABASE(), ':runtime-schema'), 10)")->fetchColumn();
        if ((int) $lock !== 1) throw new RuntimeException('Another schema migration is running.');
        $locked = true;
    }
    $columns = $pdo->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products'")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    if (!$columns) throw new RuntimeException('Missing products table. Import the base database first.');
    $pending = [];
    if (!isset($columns['price'])) {
        $pending['products.price'] = 'ALTER TABLE products ADD COLUMN price DECIMAL(15,0) NULL AFTER sku';
    } elseif (strtolower($columns['price']['COLUMN_TYPE']) !== 'decimal(15,0)' || $columns['price']['IS_NULLABLE'] !== 'YES') {
        throw new RuntimeException('products.price differs from DECIMAL(15,0) NULL. Review existing values and migrate manually; no automatic conversion performed.');
    }
    if (!isset($columns['show_price'])) {
        $pending['products.show_price'] = 'ALTER TABLE products ADD COLUMN show_price TINYINT(1) NOT NULL DEFAULT 0 AFTER price';
    }
    $settings = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='settings'")->fetchColumn();
    if (!$settings) $pending['settings'] = file_get_contents(__DIR__ . '/../database/create_settings_table.sql');
    if (!$pending) {
        echo "Schema ready; no changes required.\n";
    } else {
        foreach ($pending as $name => $sql) {
            if ($mode === '--apply') {
                $pdo->exec($sql);
                echo "Applied: $name\n";
            } else echo "Pending: $name\n";
        }
        if ($mode === '--check') {
            fwrite(STDERR, "Backup database, then run with --apply before deploying the application.\n");
            exit(2);
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    if ($locked) $pdo->query("SELECT RELEASE_LOCK(CONCAT(DATABASE(), ':runtime-schema'))");
}
