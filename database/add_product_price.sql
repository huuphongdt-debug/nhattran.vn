-- SQL cũ bổ sung giá; ưu tiên scripts/migrate-runtime-schema.php để kiểm tra cột trước khi thêm.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
ALTER TABLE products
    ADD COLUMN price DECIMAL(15,0) NULL AFTER sku,
    ADD COLUMN show_price TINYINT(1) NOT NULL DEFAULT 0 AFTER price;

ALTER TABLE products MODIFY COLUMN price DECIMAL(15,0) NULL;
