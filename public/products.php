<?php
/*
 * Danh sách sản phẩm công khai; dùng ProductCategoryController và ProductController.
 * Bộ lọc, cây danh mục và phân trang cần giữ cùng tham số khi tạo các liên kết.
 * JavaScript products.js hỗ trợ tương tác danh mục và hộp liên hệ.
 */


require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . "/../app/Controllers/ProductCategoryController.php";
require_once __DIR__ . "/../app/Controllers/ProductController.php";

/*
|--------------------------------------------------------------------------
| CONTROLLER
|--------------------------------------------------------------------------
*/

$categoryController = new ProductCategoryController($pdo);
$productController  = new ProductController($pdo);


/*
|--------------------------------------------------------------------------
| LẤY CÂY DANH MỤC
|--------------------------------------------------------------------------
*/

$categories = $categoryController->tree();


/*
|--------------------------------------------------------------------------
| CATEGORY ĐANG ĐƯỢC CHỌN
|--------------------------------------------------------------------------
*/

$selectedCategoryId = isset($_GET['category'])
    ? (int) $_GET['category']
    : 0;

$keyword = trim((string) ($_GET['keyword'] ?? ''));

function productImageUrl(string $path): string
{
    $path = trim($path);

    if ($path === '' || preg_match('#^(?:https?:)?//#i', $path) || str_starts_with($path, '/')) {
        return $path;
    }

    return '../' . ltrim($path, '/');
}

/** Lấy ID của danh mục đang chọn cùng toàn bộ danh mục con. */
function selectedCategoryIds(array $categories, int $selectedId): array
{
    foreach ($categories as $category) {
        if ((int) ($category['id'] ?? 0) === $selectedId) {
            $ids = [(int) $category['id']];
            foreach ($category['children'] ?? [] as $child) {
                $ids = array_merge($ids, selectedCategoryIds([$child], (int) $child['id']));
            }

            return $ids;
        }

        $ids = selectedCategoryIds($category['children'] ?? [], $selectedId);
        if ($ids) {
            return $ids;
        }
    }

    return [];
}


/*
|--------------------------------------------------------------------------
| LẤY DANH SÁCH SẢN PHẨM
|--------------------------------------------------------------------------
|
| category = 0
| → tất cả sản phẩm active
|
| category > 0
| → sản phẩm thuộc danh mục đang chọn
|
|--------------------------------------------------------------------------
*/

$categoryFilter = $selectedCategoryId > 0
    ? selectedCategoryIds($categories, $selectedCategoryId)
    : 0;

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$totalProducts = $productController->countPaginated($keyword, 'active', $categoryFilter ?: $selectedCategoryId);
$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$products = $productController->index(
    $keyword,
    'active',
    $categoryFilter ?: $selectedCategoryId,
    $page,
    $perPage
);


/*
|--------------------------------------------------------------------------
| CATEGORY HIỆN TẠI
|--------------------------------------------------------------------------
*/

$currentCategory = null;

if ($selectedCategoryId > 0) {
    $currentCategory = $categoryController->find(
        $selectedCategoryId
    );
}


/*
|--------------------------------------------------------------------------
| TÌM CATEGORY CHA CẦN MỞ
|--------------------------------------------------------------------------
*/

function categoryContainsSelected(array $category, int $selectedId): bool
{
    if ((int) ($category['id'] ?? 0) === $selectedId) {
        return true;
    }

    if (!empty($category['children'])) {
        foreach ($category['children'] as $child) {
            if (categoryContainsSelected($child, $selectedId)) {
                return true;
            }
        }
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| ICON DANH MỤC
|--------------------------------------------------------------------------
*/

function getCategoryIcon(string $name): string
{
    $nameLower = mb_strtolower($name, 'UTF-8');

    if (strpos($nameLower, 'plc') !== false) {
        return 'fa-microchip';
    }

    if (strpos($nameLower, 'biến tần') !== false) {
        return 'fa-gauge-high';
    }

    if (strpos($nameLower, 'motor') !== false) {
        return 'fa-gears';
    }

    if (strpos($nameLower, 'servo') !== false) {
        return 'fa-arrows-spin';
    }

    if (strpos($nameLower, 'encoder') !== false) {
        return 'fa-rotate';
    }

    if (
        strpos($nameLower, 'đóng ngắt') !== false ||
        strpos($nameLower, 'đóng cắt') !== false
    ) {
        return 'fa-toggle-on';
    }

    if (strpos($nameLower, 'tủ điện') !== false) {
        return 'fa-box';
    }

    if (strpos($nameLower, 'hàn') !== false) {
        return 'fa-fire-burner';
    }

    if (strpos($nameLower, 'cắt') !== false) {
        return 'fa-scissors';
    }

    if (strpos($nameLower, 'phun') !== false) {
        return 'fa-spray-can';
    }

    if (
        strpos($nameLower, 'khí nén') !== false ||
        strpos($nameLower, 'khí') !== false
    ) {
        return 'fa-wind';
    }

    if (
        strpos($nameLower, 'xi măng') !== false ||
        strpos($nameLower, 'xây dựng') !== false
    ) {
        return 'fa-industry';
    }

    if (
        strpos($nameLower, 'linh kiện') !== false ||
        strpos($nameLower, 'phụ kiện') !== false
    ) {
        return 'fa-screwdriver-wrench';
    }

    if (strpos($nameLower, 'thiết bị') !== false) {
        return 'fa-gear';
    }

    return 'fa-cube';
}


/*
|--------------------------------------------------------------------------
| RENDER CATEGORY TREE
|--------------------------------------------------------------------------
|
| - Danh mục có con: có nút mũi tên để thu gọn / mở rộng.
| - Click tên: đi tới danh mục.
| - Danh mục đang chọn hoặc chứa danh mục đang chọn: tự mở.
|
|--------------------------------------------------------------------------
*/

function renderCategoryTree(
    array $categories,
    int $selectedCategoryId,
    int $level = 0
): void {
    if (empty($categories)) {
        return;
    }

    $listClass = $level === 0
        ? 'category-list'
        : 'category-children-list';

    echo '<ul class="' . $listClass . '">';

    foreach ($categories as $category) {

        $categoryId = (int) ($category['id'] ?? 0);

        $categoryName = htmlspecialchars(
            (string) ($category['name'] ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $hasChildren = !empty($category['children']);

        $isSelected =
            $selectedCategoryId === $categoryId;

        $containsSelected =
            $hasChildren &&
            categoryContainsSelected(
                $category,
                $selectedCategoryId
            );

        $isOpen = $containsSelected;

        echo '<li class="category-item';

        if ($hasChildren) {
            echo ' has-children';
        }

        if ($isSelected) {
            echo ' is-selected';
        }

        if ($isOpen) {
            echo ' is-open';
        }

        echo '">';


        /*
        |----------------------------------------------------------------------
        | CATEGORY ROW
        |----------------------------------------------------------------------
        */

        echo '<div class="category-row">';


        /*
        |----------------------------------------------------------------------
        | TOGGLE
        |----------------------------------------------------------------------
        */

        if ($hasChildren) {

            echo '<button
                    type="button"
                    class="category-toggle"
                    aria-label="Mở hoặc đóng ' . $categoryName . '"
                    aria-expanded="' . ($isOpen ? 'true' : 'false') . '"
                >';

            echo '<i class="fa-solid fa-chevron-right"></i>';

            echo '</button>';

        } else {

            echo '<span class="category-toggle-placeholder"></span>';

        }


        /*
        |----------------------------------------------------------------------
        | LINK
        |----------------------------------------------------------------------
        */

        echo '<a
                href="products.php?category=' . $categoryId . '"
                class="category-link' .
                ($isSelected ? ' selected' : '') .
                '"
            >';

        echo '<span class="category-icon">
                <i class="fa-solid ' .
                getCategoryIcon(
                    (string) ($category['name'] ?? '')
                ) .
                '"></i>
              </span>';

        echo '<span class="category-name">
                ' . $categoryName . '
              </span>';

        echo '</a>';

        echo '</div>';


        /*
        |----------------------------------------------------------------------
        | CHILDREN
        |----------------------------------------------------------------------
        */

        if ($hasChildren) {

            echo '<div
                    class="category-children"
                    ' . (!$isOpen ? 'hidden' : '') . '
                >';

            renderCategoryTree(
                $category['children'],
                $selectedCategoryId,
                $level + 1
            );

            echo '</div>';
        }


        echo '</li>';
    }

    echo '</ul>';
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sản phẩm | NHAT TRAN</title>

    <!-- FONT AWESOME -->
    

    <!-- PRODUCTS CSS -->
    

<?php $includeProductStyles = true; require __DIR__ . '/includes/styles.php'; ?>
</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<?php $active = 'products'; require __DIR__ . '/includes/header.php'; ?>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="products-page">

    <?php siteHero('Sản phẩm', 'Tìm thiết bị phù hợp với nhu cầu vận hành và hệ thống của bạn.', 'THIẾT BỊ · LINH KIỆN · TỰ ĐỘNG HÓA', 'Khám phá danh mục thiết bị', ['Tìm theo tên hoặc mã', 'Lọc theo danh mục', 'Tư vấn lựa chọn'], ['Sản phẩm' => null]); ?>

    <div class="products-container">


        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->



        <!-- =====================================================
             PRODUCTS LAYOUT
        ====================================================== -->

        <div class="products-layout">


            <!-- =================================================
                 CATEGORY SIDEBAR
            ================================================== -->

            <aside class="category-sidebar">


                <div class="sidebar-header">

                    <span class="sidebar-header-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>

                    <h2>
                        Danh mục sản phẩm
                    </h2>

                </div>


                <div class="category-content">


                    <!-- TẤT CẢ SẢN PHẨM -->

                    <a
                        href="products.php"
                        class="all-products-link <?= $selectedCategoryId === 0 ? 'selected' : '' ?>"
                    >

                        <span class="category-icon">
                            <i class="fa-solid fa-house"></i>
                        </span>

                        <span class="category-name">
                            Tất cả sản phẩm
                        </span>

                        <span class="all-products-arrow">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>

                    </a>


                    <!-- CATEGORY TREE -->

                    <?php if (empty($categories)): ?>

                        <div class="category-empty">

                            <i class="fa-solid fa-folder-open"></i>

                            <span>
                                Chưa có danh mục sản phẩm.
                            </span>

                        </div>

                    <?php else: ?>

                        <?php
                        renderCategoryTree(
                            $categories,
                            $selectedCategoryId
                        );
                        ?>

                    <?php endif; ?>


                </div>

            </aside>


            <!-- =================================================
                 PRODUCT CONTENT
            ================================================== -->

            <section class="products-content">


                <!-- PRODUCT HEADER -->

                <div class="products-content-header">

                    <div>

                        <h2>
                            <?= htmlspecialchars(
                                $currentCategory['name']
                                ?? 'Tất cả sản phẩm',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h2>

                        <p>
                            <?php if ($currentCategory): ?>
                                Sản phẩm thuộc danh mục này
                            <?php else: ?>
                                Danh sách tất cả sản phẩm
                            <?php endif; ?>
                        </p>

                    </div>


                    <div class="product-count">

                        <?= $totalProducts ?>
                        sản phẩm

                    </div>

                </div>


                <!-- PRODUCT CONTENT BODY -->

                <?php if ($keyword !== ''): ?>
                <div class="search-summary">
                    Kết quả cho “<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>”
                    <a href="products.php?category=<?= $selectedCategoryId ?>">Xóa từ khóa</a>
                </div>
                <?php endif; ?>

                <div class="products-content-body">


                    <?php if (empty($products)): ?>

                        <div class="empty-products">

                            <div class="empty-icon">
                                <i class="fa-solid fa-box-open"></i>
                            </div>

                            <h3>
                                Không tìm thấy sản phẩm
                            </h3>

                            <p>
                                Hãy thử từ khóa khác hoặc chọn danh mục khác.
                            </p>

                        </div>

                    <?php else: ?>


                        <!-- PRODUCT GRID -->

                        <div class="product-grid">


                            <?php foreach ($products as $product): ?>

                                <?php

                                $productId =
                                    (int) ($product['id'] ?? 0);

                                $productName =
                                    (string) (
                                        $product['name']
                                        ?? 'Sản phẩm'
                                    );

                                $categoryName =
                                    (string) (
                                        $product['category_name']
                                        ?? 'Chưa phân loại'
                                    );

                                $sku =
                                    (string) (
                                        $product['sku']
                                        ?? ''
                                    );

                                $image =
                                    trim(
                                        (string) (
                                            $product['image']
                                            ?? ''
                                        )
                                    );

                                    /*
                                    |--------------------------------------------------------------------------
                                    | HÃNG SẢN XUẤT
                                    |--------------------------------------------------------------------------
                                    |
                                    | Ưu tiên manufacturer.
                                    | Có fallback để tránh lỗi nếu ProductController
                                    | đang trả về tên trường khác.
                                    |
                                    */

                                $manufacturer =
                                    trim(
                                        (string) (
                                            $product['manufacturer']
                                            ?? $product['manufacturer_name']
                                            ?? $product['brand']
                                            ?? $product['brand_name']
                                            ?? ''
                                        )
                                    );

                                ?>

                                <article class="product-card">


                                    <!-- ẢNH SẢN PHẨM -->

                                    <div class="product-image">

                                        <?php if (!empty($product['image'])): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                    productImageUrl($image),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $productName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                loading="lazy"
                                            >

                                        <?php else: ?>

                                            <div class="product-image-empty">

                                                <i class="fa-solid fa-box-open"></i>

                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <!-- THÔNG TIN SẢN PHẨM -->

                                    <div class="product-info">

                                            <!-- TÊN -->

                                        <div class="product-category">

                                            <?= htmlspecialchars(
                                                $categoryName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>

                                            <!-- TÊN SẢN PHẨM -->

                                        <h3 class="product-name">

                                            <?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </h3>

                                        <!-- SKU -->

                                        <?php if ($sku !== ''): ?>

                                            <div class="product-meta">

                                                <span class="product-meta-label">
                                                    SKU:
                                                </span>

                                                <span class="product-meta-value">
                                                    <?= htmlspecialchars(
                                                        $sku,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </span>

                                            </div>

                                        <?php endif; ?>

                                        <!-- HÃNG SẢN XUẤT -->

                                        <?php if ($manufacturer !== ''): ?>

                                            <div class="product-meta">

                                                <span class="product-meta-label">
                                                    Hãng:
                                                </span>

                                                <span class="product-meta-value">
                                                    <?= htmlspecialchars(
                                                        $manufacturer,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </span>

                                            </div>

                                            <?php endif; ?>

                                        </div>


                                        <!-- LIÊN HỆ -->
                                        <button
                                            type="button"
                                            class="product-contact-button"
                                            data-product-name="<?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <i class="fa-solid fa-phone"></i>
                                            <span>Liên hệ</span>
                                        </button>


                                        <a
                                            href="product-detail.php?id=<?= $productId ?>"
                                            class="product-detail-button"
                                        >
                                            Xem chi tiết
                                            <span>→</span>
                                        </a>


                                

                                </article>

                            <?php endforeach; ?>


                        </div>

                    <?php endif; ?>


                </div>

                <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Phân trang sản phẩm">
                    <?php if ($page > 1): ?><a href="?<?= htmlspecialchars(http_build_query(['category' => $selectedCategoryId, 'keyword' => $keyword, 'page' => $page - 1]), ENT_QUOTES, 'UTF-8') ?>">← Trang trước</a><?php endif; ?>
                    <span>Trang <?= $page ?> / <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?><a href="?<?= htmlspecialchars(http_build_query(['category' => $selectedCategoryId, 'keyword' => $keyword, 'page' => $page + 1]), ENT_QUOTES, 'UTF-8') ?>">Trang sau →</a><?php endif; ?>
                </nav>
                <?php endif; ?>
            </section>


        </div>

    </div>

</main>


<!-- =========================================================
     CONTACT MODAL
========================================================= -->
<div
    class="contact-modal"
    id="contactModal"
    hidden
    aria-hidden="true"
>
    <div
        class="contact-modal-overlay"
        data-contact-close
    ></div>

    <div
        class="contact-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="contactModalTitle"
    >
        <button
            type="button"
            class="contact-modal-close"
            id="contactModalClose"
            aria-label="Đóng"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="contact-modal-icon">
            <i class="fa-solid fa-headset"></i>
        </div>

        <h2 id="contactModalTitle">Liên hệ</h2>

        <p class="contact-modal-product">
            Bạn đang quan tâm:
            <strong id="contactProductName">Sản phẩm</strong>
        </p>

        <div class="contact-options">

            <!-- THAY SỐ ĐIỆN THOẠI BẰNG SỐ THẬT -->
            <a
                href="<?= siteEscape(companyLink($pdo, 'phone')) ?>"
                class="contact-option contact-phone"
            >
                <span class="contact-option-icon">
                    <i class="fa-solid fa-phone"></i>
                </span>

                <span class="contact-option-content">
                    <strong>Gọi điện thoại</strong>
                    <small><?= siteEscape(companyValue($pdo, 'company_phone')) ?></small>
                </span>

                <i class="fa-solid fa-chevron-right"></i>
            </a>


            <!-- THAY LINK ZALO BẰNG LINK ZALO THẬT -->
            <a
                href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-option contact-zalo"
            >
                <span class="contact-option-icon">
                    <strong>Z</strong>
                </span>

                <span class="contact-option-content">
                    <strong>Chat qua Zalo</strong>
                    <small>Trao đổi nhanh về sản phẩm</small>
                </span>

                <i class="fa-solid fa-chevron-right"></i>
            </a>


            <!-- THAY LINK FACEBOOK BẰNG FANPAGE THẬT -->
            <a
                href="<?= siteEscape(companyLink($pdo, 'facebook')) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-option contact-facebook"
            >
                <span class="contact-option-icon">
                    <i class="fa-brands fa-facebook-f"></i>
                </span>

                <span class="contact-option-content">
                    <strong>Facebook</strong>
                    <small>Nhắn tin qua Fanpage</small>
                </span>

                <i class="fa-solid fa-chevron-right"></i>
            </a>

        </div>

        <p class="contact-modal-note">
            Vui lòng cho chúng tôi biết sản phẩm bạn đang quan tâm
            để được tư vấn nhanh nhất.
        </p>
    </div>
</div>




<?php require __DIR__ . '/includes/footer.php'; ?><?php require __DIR__ . '/includes/scripts.php'; ?>
</body>

</html>
