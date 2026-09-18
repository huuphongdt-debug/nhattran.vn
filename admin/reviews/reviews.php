<?php
require_once __DIR__ . '/../includes/layout.php';
requireManager();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') requireAdminPost();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/ProductReview.php';
$model = new ProductReview($pdo);
$status = (string) ($_POST['status'] ?? $_GET['status'] ?? 'pending');
if (!isset(ProductReview::STATUSES[$status])) $status = 'pending';
$page = max(1, (int) ($_POST['page'] ?? $_GET['page'] ?? 1));
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $changed = $model->moderate((int) ($_POST['id'] ?? 0), (string) ($_POST['action'] ?? ''));
        $_SESSION['review_notice'] = $changed ? 'Đã cập nhật đánh giá.' : 'Đánh giá đã được xử lý hoặc không còn tồn tại.';
        header('Location: reviews.php?' . http_build_query(['status' => $status, 'page' => $page]));
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log('Review moderation: ' . $e->getMessage());
        $error = 'Không cập nhật được đánh giá. Vui lòng thử lại.';
    }
}
$result = $model->page($status, $page);
$notice = $_SESSION['review_notice'] ?? '';
unset($_SESSION['review_notice']);
?>
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kiểm duyệt đánh giá | NHAT TRAN</title>
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/reviews.css"></head><body class="admin-shell">
<?php require __DIR__ . '/../includes/shell-start.php'; ?>
<main>
    <div class="page-header"><div><h1>Đánh giá sản phẩm</h1><p>Chỉ đánh giá đã duyệt mới xuất hiện trên website.</p></div></div>
    <?php if ($notice): ?><p role="status"><?= adminEscape($notice) ?></p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= adminEscape($error) ?></p><?php endif; ?>
    <nav class="page-actions" aria-label="Trạng thái đánh giá">
        <?php foreach (ProductReview::STATUSES as $key => $label): ?>
        <a class="btn <?= $key === $status ? 'btn-primary' : 'btn-back' ?>" href="?status=<?= $key ?>" <?= $key === $status ? 'aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <p><?= $result['total'] ?> đánh giá · Trang <?= $result['page'] ?>/<?= $result['pages'] ?></p>
    <?php foreach ($result['items'] as $review): ?>
    <article class="card review-card">
        <h2><?= adminEscape($review['product_name']) ?></h2>
        <p><strong><?= adminEscape($review['reviewer_name']) ?></strong> · <?= (int) $review['rating'] ?>/5 sao · <?= adminEscape($review['created_at']) ?></p>
        <p class="review-text"><?= adminEscape($review['review_text']) ?></p>
        <div class="actions">
        <?php foreach (['approved' => 'Duyệt', 'hidden' => 'Ẩn', 'delete' => 'Xóa'] as $action => $label): ?>
            <?php if ($action === $review['status']) continue; ?>
            <?= adminPostAction('reviews.php?' . http_build_query(['action' => $action, 'id' => $review['id'], 'status' => $status, 'page' => $result['page']]), $label, $action === 'delete' ? 'btn btn-delete' : 'btn btn-edit', $action === 'delete') ?>
        <?php endforeach; ?>
        </div>
    </article>
    <?php endforeach; ?>
    <?php if (!$result['items']): ?><div class="card">Không có đánh giá trong trạng thái này.</div><?php endif; ?>
    <nav class="page-actions" aria-label="Phân trang đánh giá">
        <?php if ($result['page'] > 1): ?><a class="btn btn-back" href="?<?= adminEscape(http_build_query(['status' => $status, 'page' => $result['page'] - 1])) ?>">Trang trước</a><?php endif; ?>
        <?php if ($result['page'] < $result['pages']): ?><a class="btn btn-back" href="?<?= adminEscape(http_build_query(['status' => $status, 'page' => $result['page'] + 1])) ?>">Trang sau</a><?php endif; ?>
    </nav>
</main>
<?php require __DIR__ . '/../includes/shell-end.php'; ?></body></html>
