-- Dữ liệu banner ban đầu; kiểm tra bảng và đường dẫn ảnh trước khi sử dụng.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
INSERT INTO banners (title, subtitle, image, button_text, button_link, status, sort_order)
SELECT 'Tự động hóa vững chắc cho nhà máy hiện đại',
       'Tích hợp thiết bị điện, điều khiển và giải pháp vận hành hiệu quả cho từng dây chuyền.',
       '/NHATTRAN/uploads/banners/hero-automation.svg',
       'Khám phá giải pháp',
       'products.php',
       'active',
       1
WHERE NOT EXISTS (
    SELECT 1 FROM banners WHERE image = '/NHATTRAN/uploads/banners/hero-automation.svg'
);

INSERT INTO banners (title, subtitle, image, button_text, button_link, status, sort_order)
SELECT 'Thiết bị công nghiệp chính hãng, sẵn sàng đồng hành',
       'Danh mục sản phẩm chọn lọc cùng tư vấn kỹ thuật tận tâm từ NHAT TRAN.',
       '/NHATTRAN/uploads/banners/hero-industrial-products.svg',
       'Xem sản phẩm',
       'products.php',
       'active',
       2
WHERE NOT EXISTS (
    SELECT 1 FROM banners WHERE image = '/NHATTRAN/uploads/banners/hero-industrial-products.svg'
);
