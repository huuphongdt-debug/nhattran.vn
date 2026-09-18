<?php
/*
 * Trang chủ tổng hợp danh mục, sản phẩm, dịch vụ, bài viết, banner và nhóm thương hiệu.
 * Các controller/model chuẩn bị dữ liệu trước HTML; brand-directory.php hiển thị nhóm hãng.
 * Helper homeEscape/homeAssetUrl xử lý nội dung và đường dẫn khi xuất giao diện.
 */


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/SettingsHelper.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/../app/Controllers/ProductCategoryController.php';
require_once __DIR__ . '/../app/Controllers/ProductController.php';
require_once __DIR__ . '/../app/Controllers/ServiceController.php';
require_once __DIR__ . '/../app/Controllers/PostController.php';
require_once __DIR__ . '/../app/Models/ManufacturerGroup.php';

$categories = [];
$featuredProducts = [];
$services = [];
$latestPosts = [];
$brandSections = [];
$banners = [];

try {
    $categoryController = new ProductCategoryController($pdo);
    $productController = new ProductController($pdo);
    $serviceController = new ServiceController($pdo);
    $postController = new PostController($pdo);

    $categories = array_slice($categoryController->tree(), 0, 6);
    $featuredProducts = $productController->featured(6);
    // Hiển thị tối đa 6 dịch vụ đang hoạt động để nội dung do admin quản lý
    // được phản ánh đầy đủ trên trang chủ.
    $services = array_slice($serviceController->active(), 0, 6);
    $latestPosts = array_slice($postController->published(), 0, 3);
    $brandSections = (new ManufacturerGroup($pdo))->directory();
    $banners = $pdo->query(
        "SELECT title, subtitle, image, button_text, button_link FROM banners WHERE status = 'active' ORDER BY sort_order ASC, id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    // Trang chủ vẫn hiển thị khi database chưa có dữ liệu ban đầu.
}

function homeEscape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Ảnh sản phẩm được admin lưu tương đối với thư mục gốc dự án.
 * Chuyển sang URL đúng khi hiển thị từ thư mục public.
 */
function homeAssetUrl(?string $path): string
{
    $path = trim((string) $path);

    if ($path === '' || preg_match('#^(?:https?:)?//#i', $path) || str_starts_with($path, '/')) {
        return $path;
    }

    return '../' . ltrim($path, '/');
}

function categoryIcon(string $name): string
{
    $name = mb_strtolower($name, 'UTF-8');

    if (str_contains($name, 'xi măng')) {
        return 'fa-building';
    }

    if (str_contains($name, 'hàn') || str_contains($name, 'cắt') || str_contains($name, 'plasma')) {
        return 'fa-fire-flame-curved';
    }

    if (str_contains($name, 'phun phủ') || str_contains($name, 'phun')) {
        return 'fa-spray-can';
    }

    if (str_contains($name, 'khí nén')) {
        return 'fa-wind';
    }

    if (str_contains($name, 'linh kiện') || str_contains($name, 'phụ kiện')) {
        return 'fa-puzzle-piece';
    }

    if (str_contains($name, 'plc')) {
        return 'fa-microchip';
    }

    if (str_contains($name, 'biến tần')) {
        return 'fa-bolt';
    }

    if (str_contains($name, 'motor') || str_contains($name, 'servo')) {
        return 'fa-gears';
    }

    if (str_contains($name, 'cảm biến')) {
        return 'fa-satellite-dish';
    }

    return 'fa-industry';
}

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="NHAT TRAN cung cấp thiết bị và giải pháp điện công nghiệp, tự động hóa.">

    <title>NHAT TRAN | Thiết bị &amp; giải pháp công nghiệp</title>

    
    
    
    
    
<?php require __DIR__ . '/includes/styles.php'; ?>
</head>

<body>

<!-- =====================================================
     HEADER / MENU
===================================================== -->
<?php $active = 'index'; require __DIR__ . '/includes/header.php'; ?>

<main>

    <!-- =====================================================
         HERO
    ===================================================== -->
    <section class="hero">
        <div class="hero-slides">
            <?php if ($banners): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <article class="hero-slide <?= $index === 0 ? 'active' : '' ?>" style="background-image:linear-gradient(rgba(8,47,82,.66),rgba(8,47,82,.72)),url('<?= homeEscape($banner['image']) ?>')">
                        <div class="container hero-content"><p class="hero-label">AUTOMATIC ELECTRIC WELDING</p><h1><?= homeEscape($banner['title']) ?></h1><p class="hero-slogan"><?= nl2br(homeEscape($banner['subtitle'] ?? '')) ?></p><div class="hero-actions"><a href="<?= homeEscape($banner['button_link'] ?: 'products.php') ?>" class="btn btn-primary"><?= homeEscape($banner['button_text'] ?: 'Xem sản phẩm') ?></a></div></div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="hero-slide active"><div class="container hero-content"><p class="hero-label">AUTOMATIC ELECTRIC WELDING</p><h1>NHAT TRAN</h1><p class="hero-slogan">Giải pháp điện công nghiệp và tự động hóa<br>cho hoạt động vận hành hiệu quả.</p><div class="hero-actions"><a href="products.php" class="btn btn-primary">Xem sản phẩm</a></div></div></article>
            <?php endif; ?>
        </div>
        <?php if (count($banners) > 1): ?><div class="hero-dots" aria-label="Chọn banner"><?php foreach ($banners as $index => $banner): ?><button class="hero-dot <?= $index === 0 ? 'active' : '' ?>" type="button" aria-label="Banner <?= $index + 1 ?>"></button><?php endforeach; ?></div><?php endif; ?>
    </section>

    <!-- =====================================================
         DANH MỤC SẢN PHẨM
    ===================================================== -->
    <section class="section categories" id="san-pham">
        <div class="container">
            <h2>Danh mục sản phẩm</h2>

            <p class="section-intro">
                Khám phá các nhóm thiết bị phục vụ nhu cầu sản xuất
                và vận hành công nghiệp.
            </p>

            <div class="category-grid">
                <?php if ($categories): ?>
                    <?php foreach ($categories as $category): ?>
                        <a class="category-card" href="products.php?category=<?= (int) $category['id'] ?>">
                            <span class="category-icon" aria-hidden="true">
                                <i class="fa-solid <?= homeEscape(categoryIcon($category['name'] ?? '')) ?>"></i>
                            </span>
                            <h3><?= homeEscape($category['name'] ?? '') ?></h3>

                            <?php if (!empty($category['children'])): ?>
                                <span class="category-children">
                                    <?php foreach (array_slice($category['children'], 0, 3) as $child): ?>
                                        <span><?= homeEscape($child['name'] ?? '') ?></span>
                                    <?php endforeach; ?>

                                    <?php if (count($category['children']) > 3): ?>
                                        <span>+<?= count($category['children']) - 3 ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="category-empty">Khám phá sản phẩm theo nhóm</span>
                            <?php endif; ?>

                            <span class="category-action">Xem sản phẩm <i class="fa-solid fa-arrow-right"></i></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Danh mục sản phẩm sẽ sớm được cập nhật.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- =====================================================
         SẢN PHẨM NỔI BẬT
    ===================================================== -->
    <section class="section featured-products">
        <div class="container">
            <h2>Sản phẩm nổi bật</h2>

            <div class="product-grid">
                <?php if ($featuredProducts): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <article class="product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= homeEscape(homeAssetUrl($product['image'])) ?>" alt="<?= homeEscape($product['name'] ?? '') ?>" loading="lazy">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fa-solid fa-cube"></i>
                                </div>
                            <?php endif; ?>

                            <div class="product-body">
                                <h3><?= homeEscape($product['name'] ?? '') ?></h3>
                                <p><?= homeEscape($product['description'] ?? 'Thiết bị công nghiệp từ NHAT TRAN.') ?></p>
                                <a href="product-detail.php?id=<?= (int) $product['id'] ?>" class="btn btn-primary">Xem chi tiết</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Chưa có sản phẩm nổi bật. Hãy đánh dấu sản phẩm trong trang quản trị để hiển thị tại đây.</div>
                <?php endif; ?>
            </div>

            <a class="view-all" href="products.php">
                Xem tất cả sản phẩm <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- =====================================================
         ĐỐI TÁC THƯƠNG HIỆU
    ===================================================== -->
    <section class="section brands" id="thuong-hieu">
        <div class="container">
            <h2>Đối tác thương hiệu</h2>

            <p class="section-intro">
                NHAT TRAN cung cấp thiết bị từ các thương hiệu uy tín trong lĩnh vực công nghiệp và tự động hóa.
            </p>

            <?php require __DIR__ . '/includes/brand-directory.php'; ?>
        </div>
    </section>

    <!-- =====================================================
         DỊCH VỤ
    ===================================================== -->
    <section class="section services" id="dich-vu">
        <div class="container">
            <h2>Dịch vụ</h2>

            <p class="section-intro">
                NHAT TRAN cung cấp dịch vụ tự động hóa và hỗ trợ kỹ thuật,
                giúp doanh nghiệp lựa chọn, triển khai và vận hành thiết bị hiệu quả.
            </p>

            <div class="service-grid">
                <?php if ($services): ?>
                    <?php foreach ($services as $service): ?>
                        <article class="service-card">
                            <i class="fa-solid <?= homeEscape($service['icon'] ?: 'fa-screwdriver-wrench') ?>"></i>
                            <h3><a href="service-detail.php?id=<?= (int) $service['id'] ?>"><?= homeEscape($service['name'] ?? '') ?></a></h3>
                            <p><?= homeEscape($service['short_description'] ?? 'Dịch vụ hỗ trợ cho hoạt động công nghiệp.') ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="service-card">
                        <i class="fa-solid fa-lightbulb"></i>
                        <h3>Tư vấn thiết bị</h3>
                        <p>Đề xuất thiết bị phù hợp với nhu cầu công việc của bạn.</p>
                    </article>

                    <article class="service-card">
                        <i class="fa-solid fa-gears"></i>
                        <h3>Giải pháp tự động hóa</h3>
                        <p>Hỗ trợ định hướng giải pháp cho hệ thống công nghiệp.</p>
                    </article>

                    <article class="service-card">
                        <i class="fa-solid fa-headset"></i>
                        <h3>Hỗ trợ kỹ thuật</h3>
                        <p>Đồng hành trong quá trình sử dụng và bảo trì thiết bị.</p>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- =====================================================
         TIN TỨC
    ===================================================== -->
    <section class="section news" id="tin-tuc">
        <div class="container">
            <h2>Tin tức &amp; cập nhật</h2>

            <div class="news-grid">
                <?php if ($latestPosts): ?>
                    <?php foreach ($latestPosts as $post): ?>
                        <article class="news-card">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= homeEscape($post['image']) ?>" alt="<?= homeEscape($post['title'] ?? '') ?>" loading="lazy">
                            <?php else: ?>
                                <div class="news-placeholder">
                                    <i class="fa-regular fa-newspaper"></i>
                                </div>
                            <?php endif; ?>

                            <div>
                                <small>
                                    <?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : 'NHAT TRAN' ?>
                                </small>
                                <h3><?= homeEscape($post['title'] ?? '') ?></h3>
                                <p><?= homeEscape($post['excerpt'] ?? 'Thông tin mới nhất từ NHAT TRAN.') ?></p>
                                <a class="news-read-more" href="news-detail.php?id=<?= (int) $post['id'] ?>">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Các bài viết mới sẽ sớm được cập nhật.</div>
                <?php endif; ?>
            </div>

            <a class="view-all" href="news.php">
                Xem tất cả tin tức <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- =====================================================
         TẠI SAO CHỌN NHAT TRAN
    ===================================================== -->
    <section class="section why-us">
        <div class="container">
            <h2>Tại sao chọn chúng tôi</h2>

            <div class="why-grid">
                <article>
                    <i class="fa-solid fa-shield-halved"></i>
                    <h3>Sản phẩm rõ nguồn gốc</h3>
                    <p>Ưu tiên chất lượng và thông tin sản phẩm minh bạch.</p>
                </article>

                <article>
                    <i class="fa-solid fa-tools"></i>
                    <h3>Hỗ trợ kỹ thuật</h3>
                    <p>Tư vấn lựa chọn theo mục tiêu sử dụng thực tế.</p>
                </article>

                <article>
                    <i class="fa-solid fa-truck-fast"></i>
                    <h3>Giao hàng linh hoạt</h3>
                    <p>Hỗ trợ giao nhận theo nhu cầu của khách hàng.</p>
                </article>

                <article>
                    <i class="fa-solid fa-industry"></i>
                    <h3>Hiểu ngành công nghiệp</h3>
                    <p>Định hướng giải pháp phù hợp với vận hành doanh nghiệp.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- =====================================================
         LIÊN HỆ VÀ BẢN ĐỒ
    ===================================================== -->
    <?php require __DIR__ . '/includes/contact-section.php'; ?>

</main>

<!-- Lựa chọn phương thức liên hệ nhanh -->
<dialog class="call-dialog" id="call-options" aria-labelledby="call-dialog-title">
    <button class="call-dialog-close" type="button" data-close-call-dialog aria-label="Đóng lựa chọn liên hệ">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <i class="fa-solid fa-headset call-dialog-icon" aria-hidden="true"></i>
    <h2 id="call-dialog-title">Liên hệ NHAT TRAN</h2>
    <p>Chọn hình thức liên hệ với số <strong><?= siteEscape(companyValue($pdo, 'company_phone')) ?></strong>.</p>
    <div class="call-dialog-actions">
        <a class="call-option call-option-phone" href="<?= siteEscape(companyLink($pdo, 'phone')) ?>">
            <i class="fa-solid fa-phone"></i>
            <span><strong>Gọi điện thoại</strong><small><?= siteEscape(companyValue($pdo, 'company_phone')) ?></small></span>
        </a>
        <a class="call-option call-option-zalo" href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>" target="_blank" rel="noopener noreferrer">
            <i class="fa-solid fa-comment-dots"></i>
            <span><strong>Nhắn Zalo</strong><small><?= siteEscape(companyValue($pdo, 'company_phone')) ?></small></span>
        </a>
    </div>
</dialog>

<!-- Nút liên hệ nhanh cố định trên màn hình -->
<div class="floating-contact" aria-label="Liên hệ nhanh">
    <button class="floating-contact-button floating-phone" type="button" data-call-dialog aria-label="Gọi ngay <?= siteEscape(companyValue($pdo, 'company_phone')) ?>">
        <i class="fa-solid fa-phone" aria-hidden="true"></i>
    </button>
    <button class="floating-contact-button floating-chat" type="button" data-chat-toggle aria-expanded="false" aria-controls="quick-chatbox" aria-label="Mở hộp chat nhanh">
        <i class="fa-solid fa-comments" aria-hidden="true"></i>
    </button>
</div>

<aside class="quick-chatbox" id="quick-chatbox" aria-labelledby="quick-chatbox-title" hidden>
    <div class="quick-chatbox-header">
        <div>
            <p>NHAT TRAN</p>
            <h2 id="quick-chatbox-title">Bạn cần hỗ trợ?</h2>
        </div>
        <button type="button" data-close-chatbox aria-label="Đóng hộp chat"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
    <p class="quick-chatbox-message">Chọn kênh liên hệ, chúng tôi sẽ phản hồi sớm nhất có thể.</p>
    <div class="quick-chatbox-actions">
        <a href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-comment-dots"></i> Nhắn tin qua Zalo</a>
        <a href="<?= siteEscape(companyLink($pdo, 'email')) ?>"><i class="fa-solid fa-envelope"></i> Gửi email cho chúng tôi</a>
    </div>
</aside>

<!-- =====================================================
     FOOTER / LIÊN HỆ
===================================================== -->
<?php require __DIR__ . '/includes/footer.php'; ?>



<?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
