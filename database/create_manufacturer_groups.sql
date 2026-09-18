-- Tạo bảng nhóm hãng, liên kết nhiều–nhiều và các nhóm mặc định; cần bảng manufacturers trước.
-- SQL chạy thủ công; ghi chú không thay đổi câu lệnh hoặc dữ liệu seed.
CREATE TABLE IF NOT EXISTS manufacturer_groups (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS manufacturer_group_members (
 manufacturer_id INT UNSIGNED NOT NULL,
 group_id INT UNSIGNED NOT NULL,
 PRIMARY KEY (manufacturer_id, group_id),
 CONSTRAINT mgm_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE CASCADE,
 CONSTRAINT mgm_group FOREIGN KEY (group_id) REFERENCES manufacturer_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO manufacturer_groups (name)
SELECT 'Thiết bị tự động hóa' WHERE NOT EXISTS (SELECT 1 FROM manufacturer_groups WHERE name = 'Thiết bị tự động hóa');
INSERT INTO manufacturer_groups (name)
SELECT 'Thiết bị hàn cắt công nghiệp' WHERE NOT EXISTS (SELECT 1 FROM manufacturer_groups WHERE name = 'Thiết bị hàn cắt công nghiệp');
INSERT INTO manufacturer_groups (name)
SELECT 'Phụ kiện khí nén' WHERE NOT EXISTS (SELECT 1 FROM manufacturer_groups WHERE name = 'Phụ kiện khí nén');
