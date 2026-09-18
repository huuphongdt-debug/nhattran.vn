-- Persistent fixed-window counters; no passwords or plaintext IP addresses stored.
CREATE TABLE IF NOT EXISTS auth_throttle (
    bucket CHAR(64) PRIMARY KEY,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    INDEX (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
