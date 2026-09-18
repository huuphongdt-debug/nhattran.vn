-- Bổ sung cột image cho hãng; cần bảng manufacturers đã tồn tại. Kiểm tra cột trước khi chạy lại.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
-- Chạy một lần cho database nhattran_local.
-- Thêm đường dẫn logo cho mỗi hãng sản xuất.

ALTER TABLE manufacturers
    ADD COLUMN image VARCHAR(255) NULL AFTER slug;
