<?php
/**
 * Database configuration and PDO factory
 *
 * Reads connection parameters from environment variables (set via
 * the .env file loaded in bootstrap.php) and returns a configured
 * PDO instance with error-mode set to exceptions so all database
 * errors surface as catchable PDOException instances.
 */

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    /** Singleton PDO instance – shared across the request lifecycle */
    private static ?PDO $instance = null;

    /**
     * Returns the shared PDO connection, creating it on first call.
     *
     * @throws PDOException If the connection cannot be established.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? 'db',
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_NAME'] ?? 'calendar'
            );

            self::$instance = new PDO(
                $dsn,
                $_ENV['DB_USER'] ?? 'calendar',
                $_ENV['DB_PASS'] ?? 'calendar',
                [
                    // Throw exceptions on error instead of returning false
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Return rows as associative arrays by default
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Disable emulated prepares for real parameterised queries
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$instance;
    }

    /** Prevent instantiation – use Database::getInstance() */
    private function __construct() {}
}
