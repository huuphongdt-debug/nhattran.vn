<?php /*
 * View danh sách sản phẩm; cần dữ liệu từ products-data.php và helper của layout.php.
 * Chứa bộ lọc, bảng sản phẩm, thao tác và phân trang; không tự khởi tạo kết nối database.
 */
?><main class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            Quản lý sản phẩm
        </h1>


        <div class="page-actions">

            <a
                href="../dashboard.php"
                class="btn btn-dashboard"
            >
                <?= adminIcon('back') ?> Dashboard
            </a>


            <a
                href="product-create.php"
                class="btn btn-primary"
            >
                <?= adminIcon('plus') ?> Thêm sản phẩm
            </a>

        </div>

    </div>


    <?php require __DIR__ . '/readiness.php'; ?>

    <!-- =================================================
         THÔNG BÁO
    ================================================== -->

    <?php if ($message !== ''): ?>

        <div class="message">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         FILTER
    ================================================== -->

    <div class="filter-box">

        <form
            method="GET"
            action="products.php"
            class="filter-form"
        >


            <!-- TÌM KIẾM -->

            <div class="filter-group">

                <label for="keyword">
                    Tìm kiếm sản phẩm
                </label>

                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    class="filter-input"
                    value="<?= htmlspecialchars($keyword) ?>"
                    placeholder="Tên sản phẩm hoặc SKU..."
                >

            </div>


            <!-- DANH MỤC -->

            <div class="filter-group">

                <label for="category_id">
                    Danh mục
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    class="filter-select"
                >

                    <option value="">
                        Tất cả danh mục
                    </option>


                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= (int) $category['id'] ?>"
                            <?= (
                                $categoryId ===
                                (int) $category['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $category['name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- TRẠNG THÁI -->

            <div class="filter-group">

                <label for="status">
                    Trạng thái
                </label>

                <select
                    id="status"
                    name="status"
                    class="filter-select"
                >

                    <option value="">
                        Tất cả trạng thái
                    </option>


                    <option
                        value="active"
                        <?= $statusFilter === 'active'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Hoạt động
                    </option>


                    <option
                        value="draft"
                        <?= $statusFilter === 'draft'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Nháp
                    </option>


                    <option
                        value="inactive"
                        <?= $statusFilter === 'inactive'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Tạm ẩn
                    </option>

                </select>

            </div>


            <!-- TÌM KIẾM -->

            <button
                type="submit"
                class="btn-search"
            >
                <?= adminIcon('search') ?> Tìm kiếm
            </button>


            <!-- XÓA LỌC -->

            <a
                href="products.php"
                class="btn-reset"
            >
                <?= adminIcon('reset') ?> Xóa lọc
            </a>

        </form>


        <?php if (
            $keyword !== ''
            || $categoryId > 0
            || $statusFilter !== ''
        ): ?>

            <div class="filter-result">

                Bộ lọc đang được áp dụng.

            </div>

        <?php endif; ?>

    </div>


    <!-- =================================================
         PRODUCT BOX
    ================================================== -->

    <div class="table-box">


        <!-- SUMMARY -->

        <?php if ($totalProducts > 0): ?>

            <div class="table-summary">

                Hiển thị

                <strong>
                    <?= (($page - 1) * $perPage) + 1 ?>
                </strong>

                -

                <strong>
                    <?= min(
                        $page * $perPage,
                        $totalProducts
                    ) ?>
                </strong>

                trong tổng số

                <strong>
                    <?= $totalProducts ?>
                </strong>

                sản phẩm

            </div>

        <?php endif; ?>


        <!-- =================================================
             KHÔNG CÓ SẢN PHẨM
        ================================================== -->

        <?php if (empty($products)): ?>

            <div class="empty">

                Chưa có sản phẩm.

                <br><br>

                <a
                    href="product-create.php"
                    class="btn btn-primary"
                >
                    <?= adminIcon('plus') ?> Thêm sản phẩm đầu tiên
                </a>

            </div>


        <?php else: ?>


            <!-- =================================================
                 DESKTOP TABLE
            ================================================== -->

            <div class="table-wrapper">

                <table class="admin-products-table">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Tên sản phẩm</th>

                            <th>Danh mục</th>

                            <th>SKU</th>

                            <th>Hãng sản xuất</th>

                            <th>Giá</th>

                            <th>Giá KM</th>

                            <th>Nổi bật</th>

                            <th>Trạng thái</th>

                            <th>Thứ tự</th>

                            <th>Ngày tạo</th>

                            <th>Thao tác</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($products as $product): ?>

                            <?php

                            $productId =
                                (int) (
                                    $product['id']
                                    ?? 0
                                );

                            $productName =
                                $product['name']
                                ?? 'Chưa đặt tên';

                            $categoryName =
                                $product['category_name']
                                ?? 'Chưa phân loại';

                            $sku =
                                $product['sku']
                                ?? '';
                            
                            $manufacturer =
                                $product['manufacturer']
                                ?? '';

                            $price =
                                (float) (
                                    $product['price']
                                    ?? 0
                                );

                            $salePrice =
                                $product['sale_price']
                                ?? null;

                            $productStatus =
                                $product['status']
                                ?? 'draft';

                            $isFeatured =
                                !empty($product['is_featured']);

                            $sortOrder =
                                (int) (
                                    $product['sort_order']
                                    ?? 0
                                );

                            $createdAt =
                                $product['created_at']
                                ?? '';

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= $productId ?>

                                </td>


                                <!-- TÊN -->

                                <td>

                                    <div class="product-name">

                                        <?= htmlspecialchars(
                                            $productName
                                        ) ?>

                                    </div>

                                </td>


                                <!-- DANH MỤC -->

                                <td>

                                    <span class="category">

                                        <?= htmlspecialchars(
                                            $categoryName
                                        ) ?>

                                    </span>

                                </td>


                                <!-- SKU -->

                                <td>

                                    <?php if ($sku !== ''): ?>

                                        <span class="sku">

                                            <?= htmlspecialchars(
                                                $sku
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="no-sale">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- HÃNG SẢN XUẤT -->

                                <td>
                                    <?php
                                        $manufacturer =
                                        trim(
                                            $product['manufacturer'] ?? ''
                                        );
                                    ?>

                                    <?php if ($manufacturer !== ''): ?>

                                        <span class="manufacturer">
                                            <?= htmlspecialchars(
                                                $manufacturer
                                            ) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="no-sale">
                                            —
                                        </span>

                                    <?php endif; ?>
                                </td>


                                <!-- GIÁ -->

                                <td>

                                    <span class="price">

                                        <?= number_format(
                                            $price,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                        đ

                                    </span>

                                </td>


                                <!-- GIÁ KHUYẾN MÃI -->

                                <td>

                                    <?php if (
                                        $salePrice !== null
                                        && $salePrice !== ''
                                        && (float) $salePrice > 0
                                    ): ?>

                                        <span class="sale-price">

                                            <?= number_format(
                                                (float) $salePrice,
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                            đ

                                        </span>

                                    <?php else: ?>

                                        <span class="no-sale">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- NỔI BẬT -->

                                <td>

                                    <?php if ($isFeatured): ?>
                                        <span class="featured-badge"><?= adminIcon('star') ?> Nổi bật</span>
                                    <?php else: ?>
                                        <span class="no-sale">—</span>
                                    <?php endif; ?>

                                </td>


                                <!-- TRẠNG THÁI -->

                                <td>

                                    <?php if (
                                        $productStatus === 'active'
                                    ): ?>

                                        <span
                                            class="status status-active"
                                        >
                                            Hoạt động
                                        </span>

                                    <?php elseif (
                                        $productStatus === 'draft'
                                    ): ?>

                                        <span
                                            class="status status-draft"
                                        >
                                            Nháp
                                        </span>

                                    <?php elseif (
                                        $productStatus === 'inactive'
                                    ): ?>

                                        <span
                                            class="status status-inactive"
                                        >
                                            Tạm ẩn
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="status status-other"
                                        >
                                            <?= htmlspecialchars(
                                                $productStatus
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- THỨ TỰ -->

                                <td>

                                    <?= $sortOrder ?>

                                </td>


                                <!-- NGÀY TẠO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $createdAt
                                    ) ?>

                                </td>


                                <!-- THAO TÁC -->

                                <td>

                                    <div class="actions">

                                        <a
                                            href="product-edit.php?id=<?= $productId ?>"
                                            class="btn-edit"
                                        >
                                            <?= adminIcon('edit') ?> Sửa
                                        </a>


                                        <?= adminPostAction('product-delete.php?id=' . ($productId), (adminIcon('trash')) . ' Xóa', 'btn-delete', true) ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 MOBILE PRODUCT CARDS
            ================================================== -->

            <div class="mobile-product-list">

                <?php foreach ($products as $product): ?>

                    <?php

                    $productId =
                        (int) (
                            $product['id']
                            ?? 0
                        );

                    $productName =
                        $product['name']
                        ?? 'Chưa đặt tên';

                    $categoryName =
                        $product['category_name']
                        ?? 'Chưa phân loại';

                    $sku =
                        $product['sku']
                        ?? '';

                    $manufacturer =
                        $product['manufacturer']
                        ?? '';

                    $price =
                        (float) (
                            $product['price']
                            ?? 0
                        );

                    $salePrice =
                        $product['sale_price']
                        ?? null;

                    $productStatus =
                        $product['status']
                        ?? 'draft';

                    $isFeatured =
                        !empty($product['is_featured']);

                    $sortOrder =
                        (int) (
                            $product['sort_order']
                            ?? 0
                        );

                    ?>


                    <div class="mobile-product-card">


                        <!-- TÊN + ID -->

                        <div class="mobile-product-header">

                            <div class="mobile-product-name">

                                <?= htmlspecialchars(
                                    $productName
                                ) ?>

                            </div>

                            <?php if ($isFeatured): ?>
                                <span class="featured-badge"><?= adminIcon('star') ?> Nổi bật</span>
                            <?php endif; ?>


                            <span class="mobile-product-id">

                                #<?= $productId ?>

                            </span>

                        </div>


                        <!-- DANH MỤC -->

                        <div class="mobile-product-category">

                            <strong>
                                Danh mục:
                            </strong>

                            <?= htmlspecialchars(
                                $categoryName
                            ) ?>

                        </div>


                        <!-- SKU -->

                        <?php if ($sku !== ''): ?>

                            <div class="mobile-product-sku">

                                <?= htmlspecialchars(
                                    $sku
                                ) ?>

                            </div>

                        <?php endif; ?>

                        <?php
                            $manufacturer =
                                trim(
                                        $product['manufacturer'] ?? ''
                                    );
                        ?>

                        <?php if ($manufacturer !== ''): ?>

                            <div class="mobile-product-category">
                                <strong>
                                    Hãng sản xuất:
                                </strong>

                                <?= htmlspecialchars(
                                    $manufacturer
                                    ) ?>
                            </div>

                        <?php endif; ?>

                        <!-- GIÁ -->

                        <div class="mobile-product-prices">

                            <?php if (
                                $salePrice !== null
                                && $salePrice !== ''
                                && (float) $salePrice > 0
                            ): ?>

                                <span class="mobile-sale-price">

                                    <?= number_format(
                                        (float) $salePrice,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    đ

                                </span>


                                <span
                                    class="no-sale"
                                    style="text-decoration: line-through;"
                                >

                                    <?= number_format(
                                        $price,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    đ

                                </span>

                            <?php else: ?>

                                <span class="mobile-price">

                                    <?= number_format(
                                        $price,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                    đ

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- STATUS + THỨ TỰ -->

                        <div class="mobile-product-info">


                            <div>

                                <?php if (
                                    $productStatus === 'active'
                                ): ?>

                                    <span
                                        class="status status-active"
                                    >
                                        Hoạt động
                                    </span>

                                <?php elseif (
                                    $productStatus === 'draft'
                                ): ?>

                                    <span
                                        class="status status-draft"
                                    >
                                        Nháp
                                    </span>

                                <?php elseif (
                                    $productStatus === 'inactive'
                                ): ?>

                                    <span
                                        class="status status-inactive"
                                    >
                                        Tạm ẩn
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status status-other"
                                    >
                                        <?= htmlspecialchars(
                                            $productStatus
                                        ) ?>
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="mobile-product-order">

                                Thứ tự:

                                <strong>
                                    <?= $sortOrder ?>
                                </strong>

                            </div>

                        </div>


                        <!-- ACTIONS -->

                        <div class="mobile-product-actions">

                            <a
                                href="product-edit.php?id=<?= $productId ?>"
                                class="btn-edit"
                            >
                                <?= adminIcon('edit') ?> Sửa
                            </a>


                            <?= adminPostAction('product-delete.php?id=' . ($productId), (adminIcon('trash')) . ' Xóa', 'btn-delete', true) ?>

                        </div>


                    </div>

                <?php endforeach; ?>

            </div>


            <!-- =================================================
                 PAGINATION
            ================================================== -->

            <?php if ($totalPages > 1): ?>

                <div class="pagination">


                    <!-- TRANG TRƯỚC -->

                    <?php if ($page > 1): ?>

                        <a
                            href="?<?= http_build_query([
                                'page' =>
                                    $page - 1,
                                'keyword' =>
                                    $keyword,
                                'status' =>
                                    $statusFilter,
                                'category_id' =>
                                    $categoryId
                            ]) ?>"
                            class="pagination-btn"
                        >
                            <?= adminIcon('back') ?> Trước
                        </a>

                    <?php else: ?>

                        <span
                            class="pagination-btn disabled"
                        >
                            <?= adminIcon('back') ?> Trước
                        </span>

                    <?php endif; ?>


                    <!-- SỐ TRANG -->

                    <?php for (
                        $i = 1;
                        $i <= $totalPages;
                        $i++
                    ): ?>

                        <?php if ($i === $page): ?>

                            <span
                                class="pagination-btn active"
                            >
                                <?= $i ?>
                            </span>

                        <?php else: ?>

                            <a
                                href="?<?= http_build_query([
                                    'page' =>
                                        $i,
                                    'keyword' =>
                                        $keyword,
                                    'status' =>
                                        $statusFilter,
                                    'category_id' =>
                                        $categoryId
                                ]) ?>"
                                class="pagination-btn"
                            >
                                <?= $i ?>
                            </a>

                        <?php endif; ?>

                    <?php endfor; ?>


                    <!-- TRANG SAU -->

                    <?php if ($page < $totalPages): ?>

                        <a
                            href="?<?= http_build_query([
                                'page' =>
                                    $page + 1,
                                'keyword' =>
                                    $keyword,
                                'status' =>
                                    $statusFilter,
                                'category_id' =>
                                    $categoryId
                            ]) ?>"
                            class="pagination-btn"
                        >
                            Sau <?= adminIcon('arrow') ?>
                        </a>

                    <?php else: ?>

                        <span
                            class="pagination-btn disabled"
                        >
                            Sau <?= adminIcon('arrow') ?>
                        </span>

                    <?php endif; ?>

                </div>

            <?php endif; ?>


        <?php endif; ?>


    </div>


</main>
