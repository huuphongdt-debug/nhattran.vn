-- Tạo bảng đánh giá liên kết products; giữ kiểu khóa và ràng buộc khớp bảng sản phẩm.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    reviewer_name VARCHAR(120) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_text TEXT NOT NULL,
    status ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_reviews_product (product_id),
    CONSTRAINT fk_product_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT chk_product_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
