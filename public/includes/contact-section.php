<?php
/*
 * Partial thông tin liên hệ dùng lại trên các trang công khai.
 * Các đường dẫn gọi điện, email, bản đồ và nội dung hiển thị nằm tại đây.
 */

$companyName = companyValue($pdo, 'company_name');
$companyAddress = companyValue($pdo, 'company_address');
$companyPhone = companyValue($pdo, 'company_phone');
$companyEmail = companyValue($pdo, 'company_email');
$companyWebsite = companyValue($pdo, 'company_website');
$companyMap = companyValue($pdo, 'google_map');
$phoneLink = preg_replace('/[^0-9+]/', '', $companyPhone);
?>
<section class="section contact-section" id="lien-he">
        <div class="container">
            <div class="contact-heading">
                <p class="section-kicker">THÔNG TIN PHÁP LÝ VÀ LIÊN HỆ</p>
                <h2>Thông tin công ty</h2>
                <p>Thông tin liên hệ, giao dịch và vị trí văn phòng của Công ty TNHH Thương mại và Kỹ thuật Nhật Trần.</p>
            </div>

            <div class="contact-layout">
                <div class="contact-details">
                    <h3 class="company-legal-name"><?= siteEscape($companyName) ?></h3>

                    <ul class="company-info-list">
                        <li><i class="fa-solid fa-location-dot"></i><span><strong>Văn phòng:</strong> <?= siteEscape($companyAddress) ?></span></li>
                        <li><i class="fa-solid fa-file-invoice"></i><span><strong>Mã số thuế:</strong> <?= siteEscape(companyValue($pdo, 'company_tax_id')) ?></span></li>
                        <li><i class="fa-solid fa-building-columns"></i><span><strong>Số tài khoản:</strong> <?= siteEscape(companyValue($pdo, 'company_bank_account')) ?></span></li>
                        <li><i class="fa-solid fa-landmark"></i><span><strong>Ngân hàng:</strong> <?= siteEscape(companyValue($pdo, 'company_bank_name')) ?></span></li>
                        <li><i class="fa-solid fa-envelope"></i><span><strong>Email:</strong> <a href="mailto:<?= siteEscape($companyEmail) ?>"><?= siteEscape($companyEmail) ?></a></span></li>
                        <li><i class="fa-solid fa-globe"></i><span><strong>Website:</strong> <a href="<?= siteEscape($companyWebsite) ?>" target="_blank" rel="noopener noreferrer"><?= siteEscape($companyWebsite) ?></a></span></li>
                        <li><i class="fa-solid fa-phone-volume"></i><span><strong>Điện thoại:</strong> <a href="tel:<?= siteEscape($phoneLink) ?>"><?= siteEscape($companyPhone) ?></a></span></li>
                    </ul>

                    <a class="contact-call-button" href="tel:<?= siteEscape($phoneLink) ?>">
                        <i class="fa-solid fa-phone"></i>
                        Gọi ngay: <?= siteEscape($companyPhone) ?>
                    </a>
                </div>

                <div class="contact-map">
                    <iframe
                        title="Bản đồ vị trí Công ty NHAT TRAN"
                        src="https://www.google.com/maps?q=<?= rawurlencode($companyMap) ?>&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen>
                    </iframe>
                    <a
                        class="map-directions"
                        href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= rawurlencode($companyMap) ?>"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa-solid fa-diamond-turn-right"></i>
                        Chỉ đường trên Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>
