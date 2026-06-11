<?php
/**
 * User model
 *
 * Handles all database operations related to user accounts.
 * Passwords are never stored or returned in plain text –
 * only bcrypt hashes produced by password_hash() are persisted.
 */

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class User
{
    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * Find a user by their email address.
     *
     * @param  string      $email
     * @return array|null  Associative row array or null if not found
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find a user by their numeric ID.
     *
     * @param  int        $id
     * @return array|null Associative row array or null if not found
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT id, name, email FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /**
     * Insert a new user record.
     *
     * @param  string $name     Display name
     * @param  string $email    Must be unique (enforced by DB constraint)
     * @param  string $password Plain-text password – hashed here before storage
     * @return int              The new user's auto-increment ID
     */
    public static function create(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hash]);

        return (int) Database::getInstance()->lastInsertId();
    }

    // ── Auth helper ───────────────────────────────────────────────────────────

    /**
     * Verify a plain-text password against a stored bcrypt hash.
     *
     * @param  string $password  Plain-text input from the login form
     * @param  string $hash      Hash stored in the database
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
