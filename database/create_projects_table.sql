-- Tạo bảng dự án dùng cho model Project; status và featured phục vụ công bố/ưu tiên.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    customer VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    short_description TEXT,
    description LONGTEXT,
    main_image VARCHAR(255) DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 0,
    featured TINYINT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX projects_visibility (status, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
