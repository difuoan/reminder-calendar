<?php
/**
 * Application bootstrap
 *
 * Loaded by every entry point (public/index.php and CLI scripts).
 * Responsibilities:
 *   1. Register the Composer PSR-4 autoloader
 *   2. Parse the .env file and populate $_ENV / putenv()
 *   3. Start the PHP session (web requests only)
 */

declare(strict_types=1);

// ── Autoloader ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../vendor/autoload.php';

// ── Environment variables ─────────────────────────────────────────────────────
// 1. Parse .env file if present (local dev)
// 2. Fall back to system environment variables (Railway / production)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue; // skip comment lines
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Merge any system environment variables not already set by .env
// (PHP-FPM with clear_env=no passes them via $_SERVER / getenv())
foreach (array_keys($_SERVER) as $key) {
    if (!isset($_ENV[$key])) {
        $val = getenv($key);
        if ($val !== false) {
            $_ENV[$key] = $val;
        }
    }
}

// ── Session ───────────────────────────────────────────────────────────────────
// Only start a session for web requests (not CLI cron/migrate scripts)
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}
