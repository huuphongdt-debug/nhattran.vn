-- Tạo bảng banner; bảng phải chưa tồn tại khi chạy câu CREATE TABLE này.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT NULL,
    image VARCHAR(255) NOT NULL,
    button_text VARCHAR(100) NULL,
    button_link VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
