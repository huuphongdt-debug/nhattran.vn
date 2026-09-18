-- Bổ sung cờ nổi bật trên bảng products; kiểm tra schema để tránh thêm lại cột đã có.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
-- Cờ xác định sản phẩm xuất hiện trong mục “Sản phẩm nổi bật” ở trang chủ.
ALTER TABLE products
    ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0
    AFTER status;
