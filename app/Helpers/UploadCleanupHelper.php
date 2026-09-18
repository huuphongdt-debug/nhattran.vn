<?php
/**
 * Dọn ảnh do ứng dụng sinh sau khi xóa bản ghi thành công.
 * Giữ tệp nếu còn tham chiếu trong dữ liệu, hoặc không xác minh được tham chiếu.
 * Không quét/xóa ảnh cũ hàng loạt; chỉ nhận đường dẫn của bản ghi vừa xóa.
 */
function cleanupDeletedUpload(PDO $pdo, ?string $path): void
{
    if (!$path) return;
    $relative = preg_replace('~^/NHATTRAN/~', '', $path);
    if (!preg_match('~^uploads/(products/product_[\w.-]+|projects/project-[\w.-]+|banners/banner_[\w.-]+)\.(png|jpg|jpeg|webp)$~i', $relative)) return;
    $root = realpath(dirname(__DIR__, 2) . '/uploads');
    $file = realpath(dirname(__DIR__, 2) . '/' . $relative);
    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) return;
    try {
        // Dò cả nội dung bài viết/settings, không chỉ cột ảnh: giữ lại nếu tên tệp xuất hiện.
        // Có thể giữ dư khi hai tệp trùng tên; ưu tiên không xóa nhầm ảnh dùng chung.
        $columns = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE IN ('char','varchar','tinytext','text','mediumtext','longtext','json')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            $table = str_replace('`', '``', $column['TABLE_NAME']);
            $field = str_replace('`', '``', $column['COLUMN_NAME']);
            $query = $pdo->prepare("SELECT 1 FROM `$table` WHERE LOCATE(?, `$field`) > 0 LIMIT 1");
            $query->execute([basename($file)]);
            if ($query->fetchColumn()) return;
        }
        if (!unlink($file)) error_log('Upload cleanup: unable to remove generated image');
    } catch (Throwable $error) {
        error_log('Upload cleanup deferred: ' . $error->getMessage());
    }
}
