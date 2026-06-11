-- ============================================================
-- Migration 002: Create users table
--
-- Stores registered user accounts.
-- Passwords are stored as bcrypt hashes (password_hash() in PHP).
-- Email must be unique as it doubles as the login identifier.
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100)    NOT NULL                    COMMENT 'Display name shown in the navigation bar',
    email         VARCHAR(255)    NOT NULL                    COMMENT 'Used for login and as the default reminder recipient',
    password_hash VARCHAR(255)    NOT NULL                    COMMENT 'bcrypt hash produced by password_hash()',
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
