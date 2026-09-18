<?php
/*
 * Luồng dự án: kiểm tra quyền, đọc ID, xác thực CSRF cho POST, lưu/xóa qua model Project.
 * GET new hoặc ID hợp lệ mở form; các trường hợp còn lại tải danh sách.
 * Ảnh upload được kiểm tra, thu nhỏ tối đa 1200px chiều rộng và lưu WebP chất lượng 80.
 * Khi lưu thất bại, nhánh catch dọn ảnh mới và phục hồi đường dẫn ảnh gốc trong form.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
requireManager();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Project.php';

function projectEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$model = new Project($pdo);
$_SESSION['projects_csrf'] ??= bin2hex(random_bytes(32));
$token = $_SESSION['projects_csrf'];
$id = max(0, (int) ($_GET['id'] ?? 0));
$editing = isset($_GET['new']) || $id > 0;
$record = $id ? $model->find($id) : null;
if ($id && !$record) {
    http_response_code(404);
    exit('Không tìm thấy dự án.');
}
$form = $record ?? [
    'name' => '',
    'customer' => '',
    'location' => '',
    'short_description' => '',
    'description' => '',
    'main_image' => '',
    'status' => 0,
    'featured' => 0,
    'sort_order' => 0
];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !is_string($_POST['csrf'] ?? null)
        || !hash_equals($token, $_POST['csrf'])
    ) {
        http_response_code(403);
        exit('Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $model->delete((int) ($_POST['id'] ?? 0));
        header('Location: projects.php?message=deleted');
        exit;
    }
    if ($action !== 'save' || !$editing) {
        http_response_code(400);
        exit('Yêu cầu không hợp lệ.');
    }
    foreach (['name', 'customer', 'location', 'short_description', 'description'] as $field) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    $form['status'] = ($_POST['status'] ?? '') === '1' ? 1 : 0;
    $form['featured'] = isset($_POST['featured']) ? 1 : 0;
    $form['sort_order'] = max(0, min(999999, (int) ($_POST['sort_order'] ?? 0)));
    if ($form['name'] === '') $error = 'Vui lòng nhập tên dự án.';
    foreach (['name', 'customer', 'location'] as $field) {
        if (mb_strlen($form[$field]) > 255) $error = 'Tên dự án, khách hàng và địa điểm tối đa 255 ký tự.';
    }
    $newImage = null;
    try {
        if ($error === '') {
            $upload = $_FILES['image'] ?? null;
            if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($upload['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Không tải được ảnh. Vui lòng chọn ảnh nhỏ hơn hoặc thử lại.');
                if ($upload['size'] > 8 * 1024 * 1024) throw new RuntimeException('Ảnh tối đa 8 MB.');
                $info = @getimagesize($upload['tmp_name']);
                if (
                    !$info
                    || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)
                ) throw new RuntimeException('Chỉ nhận ảnh JPG, PNG hoặc WebP.');
                if ($info[0] * $info[1] > 16000000) throw new RuntimeException('Ảnh quá lớn. Vui lòng giảm xuống dưới 16 megapixel.');
                $source = @imagecreatefromstring(file_get_contents($upload['tmp_name']));
                if (!$source) throw new RuntimeException('Không đọc được ảnh.');
                $width = min(1200, imagesx($source));
                $height = (int) round(imagesy($source) * $width / imagesx($source));
                $scaled = imagescale($source, $width, $height);
                if (!$scaled) throw new RuntimeException('Không xử lý được ảnh.');
                $directory = __DIR__ . '/../../uploads/projects';
                if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Không tạo được thư mục ảnh.');
                $newImage = 'uploads/projects/project-' . bin2hex(random_bytes(16)) . '.webp';
                if (!imagewebp($scaled, __DIR__ . '/../../' . $newImage, 80)) throw new RuntimeException('Không lưu được ảnh.');
                imagedestroy($source);
                imagedestroy($scaled);
                $form['main_image'] = $newImage;
            } elseif (isset($_POST['remove_image'])) {
                $form['main_image'] = '';
            }
            $model->save($form, $id ?: null);
            if (($record['main_image'] ?? '') !== $form['main_image']) {
                cleanupDeletedUpload($pdo, $record['main_image'] ?? null);
            }
            header('Location: projects.php?message=saved');
            exit;
        }
    } catch (Throwable $exception) {
        if ($newImage && is_file(__DIR__ . '/../../' . $newImage)) unlink(__DIR__ . '/../../' . $newImage);
        $form['main_image'] = $record['main_image'] ?? '';
        error_log('Project save: ' . $exception->getMessage());
        $error = $exception instanceof RuntimeException && !($exception instanceof PDOException)
            ? $exception->getMessage()
            : 'Không lưu được dự án. Vui lòng thử lại.';
    }
}
$projects = $editing ? [] : $model->all();
$message = ['saved' => 'Đã lưu dự án.', 'deleted' => 'Đã xóa dự án.'][$_GET['message'] ?? ''] ?? '';

