<?php
require_once __DIR__ . '/../../app/Helpers/UploadCleanupHelper.php';

/*
 * Sửa nội dung, trạng thái, thứ tự và ảnh của một banner đã có.
 * Luồng: phân quyền → tìm banner → xử lý POST → xuất form cùng dữ liệu hiện tại.
 * CSS: admin-common.css → banners/banner-edit.css → layout.css.
 * Khung trang và icon sử dụng các include dùng chung của quản trị.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
require_once __DIR__ . '/../../config/database.php';

requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();


// Ưu tiên ID trên URL, sau đó mới lấy ID từ biểu mẫu; ID không hợp lệ sẽ chuyển về danh sách.
$bannerId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($bannerId <= 0) {
    header('Location: banners.php?message=' . urlencode('Banner không hợp lệ.'));
    exit;
}

// Tải dữ liệu gốc để điền form và giữ ảnh cũ nếu người dùng không chọn ảnh thay thế.
$stmt = $pdo->prepare('SELECT * FROM banners WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $bannerId]);
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banner) {
    header('Location: banners.php?message=' . urlencode('Không tìm thấy banner.'));
    exit;
}

$error = '';

/**
 * Lưu ảnh mới khi quản trị viên chọn thay thế ảnh banner hiện tại.
 * Không chọn file: trả null, không gán lỗi; gặp lỗi upload: trả null và gán &$error.
 * Thành công: trả URL ảnh mới. Hàm không xóa ảnh cũ và không cập nhật database.
 * Giới hạn 5 MB, chấp nhận JPG/PNG/WebP dựa trên MIME đọc từ file tạm.
 * Khi đổi nơi lưu ảnh, kiểm tra cả đường dẫn ổ đĩa, URL và uploadBannerImage() ở banners.php.
 */
function replaceBannerImage(array $file, string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Không thể tải ảnh banner lên.';
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Ảnh banner không được lớn hơn 5 MB.';
        return null;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mimeType])) {
        $error = 'Banner phải là ảnh JPG, PNG hoặc WebP.';
        return null;
    }

    $directory = __DIR__ . '/../../uploads/banners';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = 'Không thể tạo thư mục lưu banner.';
        return null;
    }

    $filename = 'banner_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mimeType];

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        $error = 'Không thể lưu ảnh banner.';
        return null;
    }

    return '/NHATTRAN/uploads/banners/' . $filename;
}

// Chuẩn hóa dữ liệu: thứ tự không âm, trạng thái chỉ active hoặc inactive.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $buttonText = trim($_POST['button_text'] ?? '');
    $buttonLink = trim($_POST['button_link'] ?? '');
    $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    if ($title === '') {
        $error = 'Vui lòng nhập tiêu đề banner.';
    }

    // Chỉ thử upload sau khi tiêu đề hợp lệ; không chọn ảnh mới thì dùng lại ảnh gốc.
    $newImage = $error === ''
        ? replaceBannerImage($_FILES['image'] ?? [], $error)
        : null;

    if ($error === '') {
        $image = $newImage ?: $banner['image'];

        // Cập nhật đúng banner theo ID bằng tham số SQL; trường tùy chọn rỗng lưu null.
        $update = $pdo->prepare(
            'UPDATE banners
             SET title = :title,
                 subtitle = :subtitle,
                 image = :image,
                 button_text = :button_text,
                 button_link = :button_link,
                 status = :status,
                 sort_order = :sort_order
             WHERE id = :id'
        );

        $update->execute([
            ':title' => $title,
            ':subtitle' => $subtitle ?: null,
            ':image' => $image,
            ':button_text' => $buttonText ?: null,
            ':button_link' => $buttonLink ?: null,
            ':status' => $status,
            ':sort_order' => $sortOrder,
            ':id' => $bannerId,
        ]);

        if ($newImage) cleanupDeletedUpload($pdo, $banner['image']);

        // Lưu thành công: chuyển về danh sách, tránh gửi lại POST khi tải lại trang.
        header('Location: banners.php?message=' . urlencode('Đã cập nhật banner.'));
        exit;
    }

    // Khi có lỗi, giữ nội dung vừa nhập để hiển thị lại form; ảnh vẫn là ảnh đã lưu.
    $banner = array_merge($banner, [
        'title' => $title,
        'subtitle' => $subtitle,
        'button_text' => $buttonText,
        'button_link' => $buttonLink,
        'status' => $status,
        'sort_order' => $sortOrder,
    ]);
}
// HTML bên dưới: thông báo lỗi, ảnh hiện tại và form multipart để thay ảnh tùy chọn.
// Escape dữ liệu động khi đưa vào HTML; các lựa chọn được đánh dấu theo $banner.
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa banner | NHAT TRAN</title>
    <link rel="stylesheet" href="../assets/css/admin-common.css">
    <link rel="stylesheet" href="../assets/css/banners/banner-edit.css">
<link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main class="container">
    <div class="page-header"><h1>Chỉnh sửa banner</h1><a class="btn btn-muted" href="banners.php"><?= adminIcon('back') ?> Quay lại danh sách</a></div>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <section class="card">
        <div class="preview"><img src="<?= htmlspecialchars($banner['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?>"><p>Ảnh banner hiện tại</p></div>
        <form method="post" enctype="multipart/form-data">
            <?php echo adminCsrfField(); ?>
            <input type="hidden" name="id" value="<?= (int) $banner['id'] ?>">
            <div class="form-grid">
                <label class="form-group">Tiêu đề *<input name="title" value="<?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                <label class="form-group">Thứ tự hiển thị<input name="sort_order" type="number" min="0" value="<?= (int) $banner['sort_order'] ?>"></label>
                <label class="form-group full">Mô tả<textarea name="subtitle"><?= htmlspecialchars($banner['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <label class="form-group">Nút CTA
                    <select name="button_text">
                        <option value="Xem sản phẩm" <?= ($banner['button_text'] ?? '') === 'Xem sản phẩm' ? 'selected' : '' ?>>Xem sản phẩm</option>
                        <option value="Liên hệ ngay" <?= ($banner['button_text'] ?? '') === 'Liên hệ ngay' ? 'selected' : '' ?>>Liên hệ ngay</option>
                        <option value="Xem dịch vụ" <?= ($banner['button_text'] ?? '') === 'Xem dịch vụ' ? 'selected' : '' ?>>Xem dịch vụ</option>
                        <option value="Xem tin tức" <?= ($banner['button_text'] ?? '') === 'Xem tin tức' ? 'selected' : '' ?>>Xem tin tức</option>
                    </select>
                </label>
                <label class="form-group">Liên kết CTA
                    <select name="button_link">
                        <option value="products.php" <?= ($banner['button_link'] ?? '') === 'products.php' ? 'selected' : '' ?>>Trang sản phẩm</option>
                        <option value="#lien-he" <?= ($banner['button_link'] ?? '') === '#lien-he' ? 'selected' : '' ?>>Phần liên hệ</option>
                        <option value="#dich-vu" <?= ($banner['button_link'] ?? '') === '#dich-vu' ? 'selected' : '' ?>>Phần dịch vụ</option>
                        <option value="#tin-tuc" <?= ($banner['button_link'] ?? '') === '#tin-tuc' ? 'selected' : '' ?>>Phần tin tức</option>
                    </select>
                </label>
                <label class="form-group">Trạng thái<select name="status"><option value="active" <?= $banner['status'] === 'active' ? 'selected' : '' ?>>Đang hiển thị</option><option value="inactive" <?= $banner['status'] === 'inactive' ? 'selected' : '' ?>>Đang ẩn</option></select></label>
                <label class="form-group">Thay ảnh banner<input name="image" type="file" accept="image/jpeg,image/png,image/webp"><small>Để trống nếu giữ ảnh hiện tại. JPG, PNG hoặc WebP, tối đa 5 MB.</small></label>
            </div>
            <div class="actions"><button class="btn btn-primary" type="submit">Lưu thay đổi</button><a class="btn btn-muted" href="banners.php">Hủy</a></div>
        </form>
    </section>
</main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>
</html>
