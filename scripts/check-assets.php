<?php
/*
 * Kiểm tra tham chiếu CSS/JS tĩnh trong admin/public; không kết nối database.
 * Bỏ qua URL ngoài; báo file thiếu qua STDERR và mã thoát khác 0.
 */

// Read-only verification of static CSS/JS references. No database connection.
$root = dirname(__DIR__);
$missing = [];
$checked = 0;
foreach (['admin', 'public'] as $directory) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/' . $directory,
        FilesystemIterator::SKIP_DOTS
    ));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        preg_match_all('/(?:href|src)=["\']([^"\'<>]+\.(?:css|js))["\']/', file_get_contents($file->getPathname()), $matches);
        foreach ($matches[1] as $asset) {
            if (preg_match('#^(https?:)?//#', $asset)) continue;
            $base = $file->getPath();
            if (str_contains(str_replace('\\', '/', $base), '/public/includes')) $base = $root . '/public';
            $checked++;
            if (!is_file($base . '/' . $asset)) $missing[] = $file->getPathname() . ' -> ' . $asset;
        }
    }
}
foreach ($missing as $message) fwrite(STDERR, $message . PHP_EOL);
echo "Static asset references checked: $checked; missing: " . count($missing) . PHP_EOL;
exit($missing ? 1 : 0);
