-- Select the configured BIMBoleh database before importing this migration.

CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at BIGINT UNSIGNED NOT NULL,
    blocked_until BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at BIGINT UNSIGNED NOT NULL,
    INDEX idx_login_attempts_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
