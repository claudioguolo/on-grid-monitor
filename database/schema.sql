CREATE TABLE IF NOT EXISTS readings (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 received_at VARCHAR(40) NOT NULL,
 source VARCHAR(255) NOT NULL,
 method VARCHAR(16) NOT NULL,
 content_type VARCHAR(255) NOT NULL,
 query TEXT NOT NULL,
 body LONGBLOB NOT NULL,
 parsed LONGTEXT NOT NULL,
 import_key CHAR(64) DEFAULT NULL,
 UNIQUE KEY uq_import_key (import_key),
 INDEX idx_received_at (received_at, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
