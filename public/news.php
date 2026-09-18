<?php
/*
 * Danh sách tin tức lấy các bài đã công bố qua PostController::published().
 * Giữ các helper escape khi xuất tiêu đề, mô tả và đường dẫn vào HTML.
 */


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/../app/Controllers/PostController.php';

$posts = [];

try {
    $posts = (new PostController($pdo))->published();
} catch (Throwable $e) {
    // Trang vẫn hiển thị khi chưa có dữ liệu bài viết.
}

function newsEscape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tin tức | NHAT TRAN</title>
    
    
    
    
    
<?php require __DIR__ . '/includes/styles.php'; ?>
</head>
<body>
<?php $active = 'news'; require __DIR__ . '/includes/header.php'; ?>
<main>
    <?php siteHero('Tin tức & cập nhật', 'Thông tin về thiết bị công nghiệp, tự động hóa và dịch vụ kỹ thuật.', 'TIN TỨC · KIẾN THỨC · CHIA SẺ', 'Góc thông tin Nhật Trần', ['Thiết bị công nghiệp', 'Kiến thức kỹ thuật', 'Hoạt động công ty'], ['Tin tức' => null]); ?>
    <section class="section news-page"><div class="container">
        <div class="news-grid">
            <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                    <article class="news-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= newsEscape($post['image']) ?>" alt="<?= newsEscape($post['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="news-placeholder"><i class="fa-regular fa-newspaper"></i></div>
                        <?php endif; ?>
                        <div>
                            <small><?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : 'NHAT TRAN' ?></small>
                            <h2><?= newsEscape($post['title']) ?></h2>
                            <p><?= newsEscape($post['excerpt'] ?? '') ?></p>
                            <a class="news-read-more" href="news-detail.php?id=<?= (int) $post['id'] ?>">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">Các bài viết mới sẽ sớm được cập nhật.</div>
            <?php endif; ?>
        </div>
        <p class="news-back"><a class="btn support-outline" href="index.php">← Quay lại trang chủ</a></p>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?><?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
