<?php
require __DIR__ . '/backup-common.php';
$options = getopt('', ['output:', 'mysqldump:']);
if (!isset($options['output'], $options['mysqldump'])) {
    fwrite(STDERR, "Usage: php scripts/backup.php --output=OUTSIDE_WEB_ROOT --mysqldump=PATH\n"); exit(1);
}
try {
    $base = backupDirectory($options['output']);
    require __DIR__ . '/../config/database.php';
    $directory = $base . '/nhattran-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4));
    if (!mkdir($directory, 0700)) throw new RuntimeException('Cannot create snapshot');
    // No --databases: the dump must not select or create the production database on restore.
    backupProcess([$options['mysqldump'], '--host='.$host, '--port='.$port, '--user='.$username, '--single-transaction', '--quick', '--no-tablespaces', '--set-gtid-purged=OFF', '--default-character-set=utf8mb4', '--routines', '--events', $dbname], $password, $directory . '/database.sql');
    $files = ['database.sql' => ['bytes' => filesize($directory . '/database.sql'), 'sha256' => hash_file('sha256', $directory . '/database.sql')]];
    $source = realpath(__DIR__ . '/../uploads');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isLink()) throw new RuntimeException('Symlink found in uploads; inspect before backup');
        if (!$file->isFile()) continue;
        $relative = 'uploads/' . str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
        $target = $directory . '/' . $relative;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true);
        $before = hash_file('sha256', $file->getPathname());
        if (!copy($file->getPathname(), $target)) throw new RuntimeException('Cannot copy upload');
        $after = hash_file('sha256', $target);
        if ($before !== $after || $before !== hash_file('sha256', $file->getPathname())) throw new RuntimeException('Upload changed during backup; retry during maintenance');
        $files[$relative] = ['bytes' => filesize($target), 'sha256' => $after];
    }
    $counts = [];
    foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $counts[$table] = (int) $pdo->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    }
    $manifest = ['version' => 1, 'created_utc' => gmdate('c'), 'database' => $dbname, 'row_counts' => $counts, 'files' => $files];
    file_put_contents($directory . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    backupVerify($directory);
    echo "Verified backup: $directory\nFiles: " . count($files) . "\n";
} catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
