-- Dữ liệu banner công ty; tham chiếu các ảnh đã có trong uploads.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
-- Ba banner dùng ảnh bảng hiệu công ty đã có trong uploads/images.
-- Chạy file này sau khi đã tạo bảng banners.

INSERT INTO banners (title, subtitle, image, button_text, button_link, status, sort_order)
SELECT 'NHAT TRAN - Đồng hành cùng công nghiệp Việt',
       'Cung cấp thiết bị điện công nghiệp và giải pháp tự động hóa đáng tin cậy cho doanh nghiệp.',
       '/NHATTRAN/uploads/images/BIEN%20HIEU%20CONG%20TY.png',
       'Về NHAT TRAN',
    '#lien-he',
       'active',
       1
WHERE NOT EXISTS (
    SELECT 1 FROM banners WHERE title = 'NHAT TRAN - Đồng hành cùng công nghiệp Việt'
);

INSERT INTO banners (title, subtitle, image, button_text, button_link, status, sort_order)
SELECT 'Thiết bị điện công nghiệp chính hãng',
       'Lựa chọn sản phẩm phù hợp để hệ thống vận hành ổn định, an toàn và hiệu quả hơn.',
       '/NHATTRAN/uploads/images/BIEN%20HIEU%20CONG%20TY.png',
       'Xem sản phẩm',
       'products.php',
       'active',
       2
WHERE NOT EXISTS (
    SELECT 1 FROM banners WHERE title = 'Thiết bị điện công nghiệp chính hãng'
);

INSERT INTO banners (title, subtitle, image, button_text, button_link, status, sort_order)
SELECT 'Giải pháp tự động hóa cho nhà máy hiện đại',
       'Tư vấn, tích hợp và hỗ trợ kỹ thuật theo nhu cầu thực tế của từng dây chuyền.',
       '/NHATTRAN/uploads/images/BIEN%20HIEU%20CONG%20TY.png',
       'Nhận tư vấn',
       '#lien-he',
       'active',
       3
WHERE NOT EXISTS (
    SELECT 1 FROM banners WHERE title = 'Giải pháp tự động hóa cho nhà máy hiện đại'
);
