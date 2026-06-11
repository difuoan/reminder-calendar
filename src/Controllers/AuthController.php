<?php
/**
 * AuthController
 *
 * Handles user registration, login and logout.
 * All form input is validated server-side before touching the database.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    // ── Registration ──────────────────────────────────────────────────────────

    /** Render the registration form. */
    public function showRegister(array $params = []): void
    {
        $pageTitle = 'Registrieren';
        $errors    = [];
        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Process the registration form submission.
     *
     * Validates input, checks for duplicate email, creates the user
     * account and immediately logs them in.
     */
    public function register(array $params = []): void
    {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';
        $errors   = [];

        // ── Validation ────────────────────────────────────────────────────────
        if ($name === '') {
            $errors['name'] = 'Name ist erforderlich.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Bitte eine gültige E-Mail-Adresse eingeben.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        }

        // Check for duplicate email only if the format is valid
        if (empty($errors['email']) && User::findByEmail($email) !== null) {
            $errors['email'] = 'Diese E-Mail-Adresse ist bereits registriert.';
        }

        if (!empty($errors)) {
            $pageTitle = 'Registrieren';
            require __DIR__ . '/../Views/layout.php';
            return;
        }

        // ── Create account and auto-login ─────────────────────────────────────
        $userId = User::create($name, $email, $password);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_name'] = $name;

        header('Location: /calendar');
        exit;
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    /** Render the login form. */
    public function showLogin(array $params = []): void
    {
        // Already logged in? Redirect straight to the calendar
        if (!empty($_SESSION['user_id'])) {
            header('Location: /calendar');
            exit;
        }

        $pageTitle = 'Anmelden';
        $errors    = [];
        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Process the login form submission.
     *
     * Uses a deliberately vague error message ("credentials invalid")
     * to avoid leaking whether an email address exists in the system.
     */
    public function login(array $params = []): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';
        $errors   = [];

        $user = User::findByEmail($email);

        // Verify credentials – intentionally same message for wrong email OR password
        if ($user === null || !User::verifyPassword($password, $user['password_hash'])) {
            $errors['general'] = 'E-Mail-Adresse oder Passwort ist falsch.';
            $pageTitle = 'Anmelden';
            require __DIR__ . '/../Views/layout.php';
            return;
        }

        // Regenerate session ID after privilege change (prevents session fixation)
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        // Send the user back to the page they were trying to reach, or the calendar
        $redirect = $_SESSION['redirect_after_login'] ?? '/calendar';
        unset($_SESSION['redirect_after_login']);

        header("Location: $redirect");
        exit;
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    /**
     * Destroy the session and redirect to the home page.
     */
    public function logout(array $params = []): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
