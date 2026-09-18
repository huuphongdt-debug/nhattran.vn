<?php
/*
 * Footer dùng chung cho trang công khai; nội dung và liên kết được xuất trong partial này.
 * Được nạp từ khung trang hoặc trang chủ; không tự mở kết nối mới tại nơi include.
 */

$footerName = companyValue($pdo, 'company_name');
$footerPhone = companyValue($pdo, 'company_phone');
$footerEmail = companyValue($pdo, 'company_email');
$footerAddress = companyValue($pdo, 'company_address');
$footerPhoneLink = preg_replace('/[^0-9+]/', '', $footerPhone);
?>
<footer>
    <div class="container footer-grid">
        <div class="footer-brand">
            <img src="../uploads/logo/LOGO%20NHATTRAN%202025.png" alt="NHAT TRAN" class="footer-logo">
            <h3 class="footer-company-name"><?= siteEscape($footerName) ?></h3>
            <p class="footer-company-legal"><?= siteEscape($footerName) ?></p>
            <div class="footer-support-numbers">
                <a href="tel:<?= siteEscape($footerPhoneLink) ?>"><span>Hotline:</span> <?= siteEscape($footerPhone) ?></a>
                <a href="tel:0983789044"><span>Hỗ trợ kinh doanh:</span> 0983 789 044</a>
                <a href="tel:0816886622"><span>Hỗ trợ kỹ thuật 1:</span> 0816 88 66 22</a>
                <a href="tel:0869755267"><span>Hỗ trợ kỹ thuật 2:</span> 0869 755 267</a>
            </div>
        </div>
        <div class="footer-links"><h3>Liên kết</h3>
            <a href="about.php">Giới thiệu</a><a href="products.php">Sản phẩm</a><a href="services.php">Dịch vụ</a><a href="projects.php">Dự án</a><a href="news.php">Tin tức</a><a href="contact.php">Liên hệ</a>
        </div>
        <div class="footer-links"><h3>Hỗ trợ</h3>
            <a href="contact.php">Tư vấn sản phẩm</a><a href="support.php?topic=warranty">Chính sách bảo hành</a><a href="support.php?topic=return">Chính sách đổi trả</a><a href="support.php?topic=purchase">Hướng dẫn mua hàng</a>
        </div>
        <div class="footer-contact"><h3>Liên hệ</h3>
            <a href="tel:<?= siteEscape($footerPhoneLink) ?>"><i class="fa-solid fa-phone"></i><span><?= siteEscape($footerPhone) ?></span></a>
            <a href="mailto:<?= siteEscape($footerEmail) ?>"><i class="fa-solid fa-envelope"></i><span><?= siteEscape($footerEmail) ?></span></a>
            <a href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= rawurlencode($footerAddress) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-location-dot"></i><span><?= siteEscape($footerAddress) ?></span></a>
        </div>
    </div>
    <div class="container footer-social" aria-label="Kênh liên hệ"><a href="<?= siteEscape(companyLink($pdo, 'zalo')) ?>" target="_blank" rel="noopener noreferrer" aria-label="Nhắn Zalo cho NHAT TRAN"><span class="zalo-icon">Zalo</span></a><a href="<?= siteEscape(companyLink($pdo, 'email')) ?>" aria-label="Gửi email cho NHAT TRAN"><i class="fa-solid fa-envelope"></i></a></div>
    <div class="footer-bottom">© <?= date('Y') ?> NHAT TRAN. All rights reserved.</div>
</footer>
