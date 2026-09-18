<?php

/*
 * Quản lý banner: thêm mới, đổi trạng thái và xóa bằng POST có kiểm tra CSRF,
 * sau đó tải danh sách và xuất giao diện trong cùng file.
 * Phụ thuộc: AuthHelper (phân quyền), database.php ($pdo), shell dùng chung.
 * CSS: admin-common.css → banners/banners.css → layout.css; giữ thứ tự nạp.
 */

require_once __DIR__ . '/../../app/Helpers/SessionHelper.php'; startSiteSession();

require_once __DIR__ . '/../../app/Helpers/AuthHelper.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/UploadCleanupHelper.php';

// Kiểm tra quyền trước khi đọc/ghi dữ liệu và trước khi xuất HTML.
requireManager();
require_once __DIR__ . '/../../app/Helpers/CsrfHelper.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();


$error = '';
$message = $_GET['message'] ?? '';

/**
 * Lưu ảnh bắt buộc khi thêm banner: JPG, PNG hoặc WebP, tối đa 5 MB.
 * MIME được đọc từ file tạm; không dựa vào phần mở rộng tên file người dùng gửi.
 * Trả về URL ảnh khi thành công; trả null và gán thông báo qua &$error khi lỗi.
 * Đường dẫn ghi trên ổ đĩa và URL /NHATTRAN/uploads/banners/ là hai giá trị riêng;
 * khi đổi thư mục triển khai, cần kiểm tra cả hai và hàm trong banner-edit.php.
 */
function uploadBannerImage(array $file, string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Vui lòng chọn ảnh banner.';
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Ảnh banner không được lớn hơn 5 MB.';
        return null;
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($extensions[$mimeType])) {
        $error = 'Banner phải là ảnh JPG, PNG hoặc WebP.';
        return null;
    }

    $directory = __DIR__ . '/../../uploads/banners';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        $error = 'Không thể tạo thư mục lưu banner.';
        return null;
    }

    // Sinh tên ngẫu nhiên, lấy đuôi ảnh theo MIME đã kiểm tra.
    $filename = 'banner_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mimeType];

    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        $error = 'Không thể lưu ảnh banner.';
        return null;
    }

    return '/NHATTRAN/uploads/banners/' . $filename;
}

// Thêm mới: xử lý upload trước khi kiểm tra tiêu đề theo luồng hiện tại.
// File được lưu riêng với INSERT; hàm upload không tạo bản ghi trong database.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['toggle']) && !isset($_POST['delete'])) {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $buttonText = trim($_POST['button_text'] ?? 'Xem sản phẩm');
    $buttonLink = trim($_POST['button_link'] ?? 'products.php');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $image = uploadBannerImage($_FILES['image'] ?? [], $error);

    if ($error === '' && $title === '') {
        $error = 'Vui lòng nhập tiêu đề banner.';
    }

    if ($error === '' && $image !== null) {
        // Dùng tham số SQL; các trường tùy chọn rỗng được lưu thành null.
        // INSERT không truyền status nên trạng thái ban đầu theo mặc định của bảng.
        $stmt = $pdo->prepare(
            'INSERT INTO banners (title, subtitle, image, button_text, button_link, sort_order) VALUES (:title, :subtitle, :image, :button_text, :button_link, :sort_order)'
        );

        $stmt->execute([
            ':title' => $title,
            ':subtitle' => $subtitle ?: null,
            ':image' => $image,
            ':button_text' => $buttonText ?: null,
            ':button_link' => $buttonLink ?: null,
            ':sort_order' => $sortOrder,
        ]);

        // Chuyển về GET sau khi lưu để tải lại trang không gửi lại biểu mẫu thêm mới.
        header('Location: banners.php?message=' . urlencode('Đã thêm banner thành công.'));
        exit;
    }
}

// Trường POST toggle chứa ID cần đảo trạng thái active/inactive.
if (isset($_POST['toggle'])) {
    $id = (int) $_POST['toggle'];
    $pdo->prepare("UPDATE banners SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id")
        ->execute([':id' => $id]);
    header('Location: banners.php?message=' . urlencode('Đã cập nhật trạng thái banner.'));
    exit;
}

// Xóa bản ghi trước, rồi dọn ảnh được sinh bởi ứng dụng nếu không còn tham chiếu.
// Form thao tác bên dưới có hộp xác nhận trước khi gửi.
if (isset($_POST['delete'])) {
    $id = (int) $_POST['delete'];
    $imageQuery = $pdo->prepare('SELECT image FROM banners WHERE id = ?');
    $imageQuery->execute([$id]);
    $deletedImage = $imageQuery->fetchColumn();
    $pdo->prepare('DELETE FROM banners WHERE id = :id')->execute([':id' => $id]);
    cleanupDeletedUpload($pdo, $deletedImage === false ? null : $deletedImage);
    header('Location: banners.php?message=' . urlencode('Đã xóa banner.'));
    exit;
}

// Danh sách quản trị gồm cả banner ẩn; thứ tự nhỏ trước, cùng thứ tự thì ID lớn trước.
// Phần HTML bên dưới gồm thông báo, form upload multipart và danh sách thao tác.
// Dữ liệu động được escape khi đưa vào nội dung hoặc thuộc tính HTML.
$banners = $pdo->query('SELECT * FROM banners ORDER BY sort_order ASC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý banner | NHAT TRAN</title>
    <link rel="stylesheet" href="../assets/css/admin-common.css">
    <link rel="stylesheet" href="../assets/css/banners/banners.css">
<link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main class="container">
    <div class="page-header"><h1>Quản lý banner</h1><a class="btn btn-muted" href="../dashboard.php"><?= adminIcon('back') ?> Dashboard</a></div>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <section class="card">
        <h2>Thêm banner mới</h2>
        <form method="post" enctype="multipart/form-data">
            <?php echo adminCsrfField(); ?>
            <div class="form-grid">
                <label class="form-group">Tiêu đề *<input name="title" required placeholder="Ví dụ: Giải pháp tự động hóa"></label>
                <label class="form-group">Thứ tự hiển thị<input name="sort_order" type="number" value="0" min="0"></label>
                <label class="form-group full">Mô tả<textarea name="subtitle" placeholder="Mô tả ngắn hiển thị trên banner"></textarea></label>
                <label class="form-group">Nút CTA
                    <select name="button_text">
                        <option value="Xem sản phẩm">Xem sản phẩm</option>
                        <option value="Liên hệ ngay">Liên hệ ngay</option>
                        <option value="Xem dịch vụ">Xem dịch vụ</option>
                        <option value="Xem tin tức">Xem tin tức</option>
                    </select>
                </label>
                <label class="form-group">Liên kết CTA
                    <select name="button_link">
                        <option value="products.php">Trang sản phẩm</option>
                        <option value="#lien-he">Phần liên hệ</option>
                        <option value="#dich-vu">Phần dịch vụ</option>
                        <option value="#tin-tuc">Phần tin tức</option>
                    </select>
                </label>
                <label class="form-group full">Ảnh banner * <input name="image" type="file" accept="image/jpeg,image/png,image/webp" required><small>Khuyến nghị ảnh ngang 1920 × 700 px, tối đa 5 MB.</small></label>
            </div>
            <p><button class="btn btn-primary" type="submit"><?= adminIcon('plus') ?> Thêm banner</button></p>
        </form>
    </section>
    <section class="banner-list">
        <?php foreach ($banners as $banner): ?>
            <article class="banner-item">
                <img src="<?= htmlspecialchars($banner['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="banner-body"><h2><?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($banner['subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?></p><div class="actions"><a class="btn btn-edit" href="banner-edit.php?id=<?= (int) $banner['id'] ?>"><?= adminIcon('edit') ?> Chỉnh sửa</a><?= adminPostAction('banners.php?toggle=' . ((int) $banner['id']), ($banner['status'] === 'active' ? 'Đang hiển thị' : 'Đang ẩn'), 'btn btn-muted', false) ?><?= adminPostAction('banners.php?delete=' . ((int) $banner['id']), 'Xóa', 'btn btn-danger', true) ?></div></div>
            </article>
        <?php endforeach; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?>
</body>
</html>
