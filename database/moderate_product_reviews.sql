-- Chạy khi triển khai kiểm duyệt đánh giá; giữ nguyên trạng thái các đánh giá đã có.
ALTER TABLE product_reviews
    MODIFY status ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'pending';
