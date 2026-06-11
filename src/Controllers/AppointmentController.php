<?php
/**
 * AppointmentController
 *
 * Renders the calendar page. The actual CRUD operations are handled
 * via JSON by ApiController – this controller only serves the HTML
 * shell that Alpine.js hydrates on the client side.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Appointment;

class AppointmentController
{
    /**
     * Render the calendar/reminder page.
     * Requires an authenticated session.
     */
    public function index(array $params = []): void
    {
        AuthMiddleware::requireAuth();

        $pageTitle   = 'Erinnerungskalender';
        $offsets     = Appointment::OFFSETS;
        $recurrences = Appointment::RECURRENCES;

        require __DIR__ . '/../Views/layout.php';
    }
}
