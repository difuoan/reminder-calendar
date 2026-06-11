-- ============================================================
-- Migration 001: Create migrations tracking table
--
-- This table records which migration files have already been
-- applied so that the migrate.php script can safely skip them
-- on subsequent runs (idempotent execution).
-- ============================================================

CREATE TABLE IF NOT EXISTS migrations (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    filename   VARCHAR(255)    NOT NULL UNIQUE COMMENT 'Migration filename, e.g. 002_create_users.sql',
    applied_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
