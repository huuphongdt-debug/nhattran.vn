<?php
/*
 * Chi tiết sản phẩm hoạt động; ID không hợp lệ/không công khai trả trạng thái 404.
 * Trang còn xử lý đánh giá sản phẩm qua POST, token phiên và bảng product_reviews.
 * Schema đánh giá được chuẩn bị bằng migration, không tạo bảng trong request.
 */

require_once __DIR__ . '/../app/Helpers/SessionHelper.php'; startSiteSession();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/ProductController.php';
require_once __DIR__ . '/includes/site.php';
$id = max(0, (int) ($_GET['id'] ?? 0));
$product = $id ? (new ProductController($pdo))->show($id) : null;
if (!$product || ($product['status'] ?? '') !== 'active') {
    $product = null;
    http_response_code(404);
}
require __DIR__ . '/includes/review-submit.php';
$reviews = [];
if ($product) {
    $statement = $pdo->prepare("SELECT reviewer_name, rating, review_text, created_at FROM product_reviews WHERE product_id = :id AND status = 'approved' ORDER BY created_at DESC, id DESC");
    $statement->execute(['id' => $id]);
    $reviews = $statement->fetchAll(PDO::FETCH_ASSOC);
}
$displayPrice = $product && !empty($product['show_price']) && $product['price'] !== null
    ? number_format((float) $product['price'], 0, ',', '.') . ' đ'
    : '';
$images = [];
if ($product) {
    $statement = $pdo->prepare('SELECT image FROM product_images WHERE product_id = :id ORDER BY sort_order ASC, id ASC');
    $statement->execute(['id' => $id]);
    $images = array_values(array_filter(array_map('siteAsset', $statement->fetchAll(PDO::FETCH_COLUMN))));
    if (!$images && !empty($product['image'])) $images[] = siteAsset($product['image']);
}
siteStart($product['name'] ?? 'Không tìm thấy sản phẩm', 'products');
?>
<main>
    <?php siteHero($product['name'] ?? 'Không tìm thấy sản phẩm', $product['category_name'] ?? 'Thiết bị và giải pháp công nghiệp', 'SẢN PHẨM NHẬT TRẦN', 'Thông tin & tư vấn sản phẩm', array_values(array_filter([$product['manufacturer'] ?? '', $product['sku'] ?? '', 'Liên hệ báo giá'])), ['Sản phẩm' => 'products.php', 'Chi tiết sản phẩm' => null], true); ?>
    <section class="section"><div class="container">
    <?php if ($product): ?>
        <div class="detail-layout">
            <div>
                <?php if ($images): ?>
                    <img class="detail-photo" data-product-photo src="<?= siteEscape($images[0]) ?>" alt="<?= siteEscape($product['name']) ?>">
                    <?php if (count($images) > 1): ?><div class="detail-thumbs" aria-label="Ảnh sản phẩm">
                        <?php foreach ($images as $position => $image): ?>
                        <button type="button" data-photo-src="<?= siteEscape($image) ?>" aria-pressed="<?= $position === 0 ? 'true' : 'false' ?>" aria-label="Xem ảnh <?= $position + 1 ?>"><img src="<?= siteEscape($image) ?>" alt="" loading="lazy"></button>
                        <?php endforeach; ?>
                    </div><?php endif; ?>
                <?php else: ?><div class="detail-photo detail-placeholder">Hình ảnh đang được cập nhật</div><?php endif; ?>
            </div>
            <div class="detail-summary">
                <p class="section-kicker">THÔNG TIN SẢN PHẨM</p>
                <h2><?= siteEscape($product['name']) ?></h2>
                <p class="page-copy"><?= nl2br(siteEscape($product['description'] ?? '')) ?></p>
                <dl class="detail-facts">
                    <?php if ($displayPrice !== ''): ?><div><dt>Giá sản phẩm</dt><dd class="product-price"><?= siteEscape($displayPrice) ?></dd></div><?php endif; ?>
                    <?php foreach (['sku' => 'Mã sản phẩm', 'manufacturer' => 'Thương hiệu', 'category_name' => 'Danh mục'] as $key => $label): ?>
                        <?php if (!empty($product[$key])): ?><div><dt><?= $label ?></dt><dd><?= siteEscape($product[$key]) ?></dd></div><?php endif; ?>
                    <?php endforeach; ?>
                </dl>
                <h3>Liên hệ để được báo giá</h3>
                <p>Gửi tên hoặc mã sản phẩm để được tư vấn cấu hình và tình trạng cung cấp.</p>
                <div class="page-actions"><a class="btn btn-primary" href="<?= siteEscape(companyLink($pdo, 'phone')) ?>">Gọi <?= siteEscape(companyValue($pdo, 'company_phone')) ?></a><a class="btn page-outline" href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>" target="_blank" rel="noopener noreferrer">Tư vấn qua Zalo</a></div>
                <a href="products.php?category=<?= (int) ($product['category_id'] ?? 0) ?>">Xem sản phẩm cùng danh mục →</a>
            </div>
        </div>
        <article class="page-panel detail-content"><h2>Thông tin chi tiết</h2><div class="page-copy"><?= nl2br(siteEscape(($product['content'] ?? '') ?: 'Vui lòng liên hệ để nhận thông số kỹ thuật phù hợp với nhu cầu sử dụng.')) ?></div></article>
        <section class="page-panel product-reviews" id="danh-gia"><h2>Đánh giá sản phẩm</h2>
            <?php if ($reviews): ?><div class="review-list"><?php foreach ($reviews as $review): ?><article class="review-item"><div class="review-heading"><strong><?= siteEscape($review['reviewer_name']) ?></strong><span class="review-stars" aria-label="<?= (int) $review['rating'] ?> trên 5 sao"><?= str_repeat('★', (int) $review['rating']) ?><span><?= str_repeat('☆', 5 - (int) $review['rating']) ?></span></span></div><p><?= nl2br(siteEscape($review['review_text'])) ?></p><time datetime="<?= siteEscape($review['created_at']) ?>"><?= date('d/m/Y', strtotime($review['created_at'])) ?></time></article><?php endforeach; ?></div><?php else: ?><p class="review-empty">Sản phẩm chưa có đánh giá. Hãy là người đầu tiên chia sẻ trải nghiệm.</p><?php endif; ?>
            <?php if ($reviewError): ?><p class="review-alert" role="alert"><?= siteEscape($reviewError) ?></p><?php endif; ?>
            <?php if ($reviewNotice): ?><p role="status">Cảm ơn bạn! Đánh giá đã được gửi và đang chờ kiểm duyệt.</p><?php endif; ?>
            <p>Đánh giá sẽ hiển thị sau khi được kiểm duyệt.</p>
            <form class="review-form" method="post"><input type="hidden" name="csrf" value="<?= siteEscape($_SESSION['review_csrf']) ?>"><input type="hidden" name="submit_review" value="1"><label>Họ và tên<input name="reviewer_name" value="<?= siteEscape($reviewName) ?>" maxlength="120" required></label><label>Đánh giá <select name="rating" required><option value="">Chọn số sao</option><?php for ($rating = 5; $rating >= 1; $rating--): ?><option value="<?= $rating ?>" <?= $reviewRating === $rating ? 'selected' : '' ?>><?= $rating ?> sao</option><?php endfor; ?></select></label><label class="review-full">Nhận xét<textarea name="review_text" rows="4" maxlength="2000" required><?= siteEscape($reviewText) ?></textarea></label><button class="btn btn-primary" type="submit">Gửi đánh giá</button></form>
        </section>
    <?php else: ?>
        <div class="empty-state"><p>Sản phẩm không tồn tại hoặc hiện chưa được công bố.</p><a class="btn btn-primary" href="products.php">Xem danh sách sản phẩm</a></div>
    <?php endif; ?>
    </div></section>
</main>
<?php siteEnd(); ?>
