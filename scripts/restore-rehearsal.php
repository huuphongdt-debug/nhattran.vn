<?php
require __DIR__ . '/backup-common.php';
$options = getopt('', ['backup:', 'mysql:', 'output:']);
if (!isset($options['backup'], $options['mysql'], $options['output'])) {
    fwrite(STDERR, "Usage: php scripts/restore-rehearsal.php --backup=SNAPSHOT --mysql=PATH --output=OUTSIDE_WEB_ROOT\n"); exit(1);
}
$testDatabase = null;
try {
    $manifest = backupVerify($options['backup']);
    $sql = file_get_contents($options['backup'] . '/database.sql');
    if (preg_match('/\b(?:USE\s+|(?:CREATE|DROP|ALTER)\s+DATABASE\b)/i', $sql)
        || str_contains($sql, '`' . $manifest['database'] . '`.')) {
        throw new RuntimeException('Dump contains database routing statements; isolated restore refused.');
    }
    $base = backupDirectory($options['output']);
    require __DIR__ . '/../config/database.php';
    // Only a fresh random database is ever imported; no caller-supplied database target.
    $candidate = 'nhattran_restore_' . bin2hex(random_bytes(8));
    $pdo->exec('CREATE DATABASE `' . $candidate . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $testDatabase = $candidate;
    $directory = $base . '/' . $candidate;
    if (!mkdir($directory, 0700)) throw new RuntimeException('Cannot create restore directory');
    backupProcess([$options['mysql'], '--host='.$host, '--port='.$port, '--user='.$username, '--database='.$testDatabase, '--default-character-set=utf8mb4'], $password, $directory . '/import.log', $options['backup'] . '/database.sql');
    $restored = new PDO("mysql:host=$host;port=$port;dbname=$testDatabase;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $counts = [];
    foreach ($restored->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $counts[$table] = (int) $restored->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    }
    $expected = $manifest['row_counts'] ?? [];
    ksort($counts); ksort($expected);
    if ($counts !== $expected) throw new RuntimeException('Restored row counts differ; retry backup while application writes are paused.');
    foreach ($manifest['files'] as $relative => $info) {
        if ($relative === 'database.sql') continue;
        $target = $directory . '/' . $relative;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true);
        if (!copy($options['backup'] . '/' . $relative, $target) || hash_file('sha256', $target) !== $info['sha256']) throw new RuntimeException('Restore upload mismatch');
    }
    file_put_contents($directory . '/restore-report.json', json_encode(['backup' => realpath($options['backup']), 'tables' => $counts, 'uploads_verified' => count($manifest['files']) - 1], JSON_PRETTY_PRINT));
    echo "Restore rehearsal passed: $directory\n";
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); $failed = true; }
finally {
    // Drop only the isolated database successfully created by this exact run.
    if ($testDatabase !== null && preg_match('/^nhattran_restore_[a-f0-9]{16}$/', $testDatabase)) $pdo->exec('DROP DATABASE `' . $testDatabase . '`');
}
exit(isset($failed) ? 1 : 0);
