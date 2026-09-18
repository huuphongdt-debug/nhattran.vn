<?php
/*
 * Công cụ kiểm tra mã hóa văn bản trong các file được liệt kê trong script.
 * Dùng để phát hiện lỗi trước khi triển khai; giữ nguyên quy tắc và phạm vi kiểm tra.
 */

// Detect damaged UTF-8 and question marks inside words in literal UI text.
$root = dirname(__DIR__);
$failed = [];
$count = 0;
foreach (['public', 'admin'] as $directory) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/' . $directory,
        FilesystemIterator::SKIP_DOTS
    ));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        $source = file_get_contents($file->getPathname());
        $count++;
        if (!preg_match('//u', $source)) { $failed[] = $file->getPathname() . ': invalid UTF-8'; continue; }
        foreach (token_get_all($source) as $token) {
            if (!is_array($token) || $token[0] !== T_INLINE_HTML) continue;
            $text = strip_tags($token[1]);
            if (preg_match('/[a-zA-Z][?][a-zA-Z]|[?]{3,}/u', $text)) {
                $failed[] = $file->getPathname() . ': possible damaged UI text';
                break;
            }
        }
    }
}
foreach ($failed as $message) fwrite(STDERR, $message . PHP_EOL);
echo 'Encoding checked: ' . $count . '; failures: ' . count($failed) . PHP_EOL;
exit($failed ? 1 : 0);
