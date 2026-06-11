<?php
/**
 * AuthMiddleware
 *
 * Protects routes that require an authenticated user session.
 * Call AuthMiddleware::requireAuth() at the top of any controller
 * action that should be inaccessible to guests.
 */

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Redirect to the login page if no valid session exists.
     *
     * Stores the originally requested URL in the session so the
     * user can be sent back there after a successful login.
     */
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            // Remember where the user was trying to go
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
}
