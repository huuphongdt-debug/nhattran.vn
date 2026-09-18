<?php
/*
 * Chi tiết bài viết theo ID; chỉ hiển thị bản ghi có trạng thái published.
 * Dữ liệu được lấy trước khi dựng khung trang; nhánh không tìm thấy xử lý riêng.
 */


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/../app/Controllers/PostController.php';

$postId = (int) ($_GET['id'] ?? 0);
$post = null;

if ($postId > 0) {
    try {
        $post = (new PostController($pdo))->find($postId);
    } catch (Throwable $e) {
        $post = null;
    }
}

if (!$post || ($post['status'] ?? '') !== 'published') {
    $post = null;
    http_response_code(404);
}

function detailEscape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $post ? detailEscape($post['title']) . ' | NHAT TRAN' : 'Không tìm thấy bài viết | NHAT TRAN' ?></title>
    
    
    
    
    
<?php require __DIR__ . '/includes/styles.php'; ?>
</head>
<body>
<?php $active = 'news'; require __DIR__ . '/includes/header.php'; ?>
<main>
    <?php siteHero($post['title'] ?? 'Không tìm thấy bài viết', '', 'TIN TỨC NHẬT TRẦN', 'Kiến thức & thông tin công nghiệp', ['Thiết bị', 'Tự động hóa', 'Kỹ thuật'], ['Tin tức' => 'news.php', 'Chi tiết bài viết' => null], true); ?>
    <section class="section article-page">
    <article class="container article-content">
        <?php if ($post): ?>
            <a class="article-back" href="news.php">← Tất cả tin tức</a>
            <p class="article-date"><i class="fa-regular fa-calendar"></i> <?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : 'NHAT TRAN' ?></p>
            <?php if (!empty($post['image'])): ?>
                <img class="article-image" src="<?= detailEscape($post['image']) ?>" alt="<?= detailEscape($post['title']) ?>">
            <?php endif; ?>
            <?php if (!empty($post['excerpt'])): ?>
                <p class="article-excerpt"><?= detailEscape($post['excerpt']) ?></p>
            <?php endif; ?>
            <div class="article-body"><?= nl2br(detailEscape($post['content'] ?? $post['excerpt'] ?? '')) ?></div>
        <?php else: ?>
            <p class="article-excerpt">Bài viết có thể chưa được xuất bản hoặc không còn tồn tại.</p>
            <a class="btn btn-primary" href="news.php">Quay lại Tin tức</a>
        <?php endif; ?>
    </article>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?><?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
