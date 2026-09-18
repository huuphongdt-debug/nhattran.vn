<?php
/*
 * Hỗ trợ upload logo hãng cho form thêm/sửa.
 * Trả đường dẫn ảnh hoặc null; thông báo lỗi truyền qua tham chiếu $error.
 * Giữ kiểm tra file ở phía máy chủ, độc lập với thuộc tính accept của ô chọn ảnh.
 */


/**
 * Lưu logo hãng sản xuất và trả về đường dẫn public để lưu vào database.
 * Trả về null khi người dùng chưa chọn file.
 */
function uploadManufacturerLogo(?array $file, string &$error): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Không thể tải logo lên. Vui lòng thử lại.';
        return null;
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        $error = 'Logo không được lớn hơn 2 MB.';
        return null;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mimeType])) {
        $error = 'Logo phải là ảnh JPG, PNG hoặc WebP.';
        return null;
    }

    $directory = __DIR__ . '/../../uploads/brands';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = 'Không thể tạo thư mục lưu logo.';
        return null;
    }

    $filename = 'manufacturer_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mimeType];

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        $error = 'Không thể lưu logo. Vui lòng thử lại.';
        return null;
    }

    return '/NHATTRAN/uploads/brands/' . $filename;
}
