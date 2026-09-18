<?php
/*
 * Trang giới thiệu dùng khung site.php; nội dung nằm trực tiếp trong template.
 * Ảnh có srcset và sizes để trình duyệt chọn kích thước theo viewport; giữ đồng bộ với file ảnh.
 */
 require_once __DIR__ . '/includes/site.php'; siteStart('Giới thiệu', 'about'); ?>
<main>
    <?php siteHero('Về Nhật Trần', 'Đồng hành cùng doanh nghiệp trong từng giải pháp công nghiệp.', 'THIẾT BỊ · KỸ THUẬT · GIẢI PHÁP', 'Công ty TNHH Thương mại và Kỹ thuật Nhật Trần', ['Điện công nghiệp', 'Tự động hóa', 'Hỗ trợ kỹ thuật'], ['Giới thiệu' => null]); ?>
    <section class="section"><div class="container detail-layout">
        <img class="page-photo"
             src="../uploads/images/about-automation-960.webp"
             srcset="../uploads/images/about-automation-600.webp 600w, ../uploads/images/about-automation-960.webp 960w"
             sizes="(max-width: 700px) calc(100vw - 40px), (max-width: 1180px) calc((100vw - 80px) / 2), 570px"
             width="960" height="640" decoding="async"
             alt="Minh họa tủ điều khiển điện, cánh tay robot và động cơ công nghiệp">
        <div class="detail-summary"><p class="section-kicker">VỀ NHAT TRAN</p><h2>Thiết bị và giải pháp kỹ thuật</h2><p>NHAT TRAN cung cấp thiết bị và giải pháp điện công nghiệp, tự động hóa; hỗ trợ doanh nghiệp lựa chọn thiết bị phù hợp với nhu cầu vận hành.</p><p>Chúng tôi tiếp nhận yêu cầu về sản phẩm, tư vấn kỹ thuật và dịch vụ công nghiệp tại Đà Nẵng.</p><div class="page-actions"><a class="btn btn-primary" href="products.php">Khám phá sản phẩm</a><a class="btn page-outline" href="contact.php">Liên hệ chúng tôi</a></div></div>
    </div></section>
</main>
<?php siteEnd(); ?>
