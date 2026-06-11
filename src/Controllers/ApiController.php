<?php
/**
 * ApiController
 *
 * Provides a JSON REST API for appointment CRUD.
 * All endpoints require an authenticated session; unauthenticated
 * requests receive a 401 JSON response instead of a redirect.
 *
 * Endpoints:
 *   GET    /api/appointments         – list all appointments for the current user
 *   POST   /api/appointments         – create a new appointment
 *   PUT    /api/appointments/:id     – update an existing appointment
 *   DELETE /api/appointments/:id     – delete an appointment
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Appointment;

class ApiController
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Send a JSON response and terminate execution.
     *
     * @param mixed $data       Any JSON-serialisable value
     * @param int   $statusCode HTTP status code (default 200)
     */
    private function json(mixed $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Verify session auth for API calls.
     * Returns 401 JSON instead of redirecting (API callers expect JSON).
     */
    private function requireAuth(): int
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Nicht angemeldet.'], 401);
        }
        return (int) $_SESSION['user_id'];
    }

    /**
     * Parse the raw request body as JSON (used for PUT requests,
     * which don't populate $_POST in PHP).
     */
    private function jsonBody(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body ?: '{}', true) ?? [];
    }

    /**
     * Validate and sanitise the appointment fields common to
     * both create and update operations.
     *
     * @return array ['errors' => [...]] on failure, or the cleaned data on success
     */
    private function validateFields(array $data): array
    {
        $errors = [];

        $title         = trim($data['title'] ?? '');
        $date          = trim($data['date']  ?? '');
        $reminderOffset = $data['reminder_offset'] ?? '';
        $recurrence    = $data['recurrence'] ?? '';

        if ($title === '') {
            $errors['title'] = 'Bezeichnung ist erforderlich.';
        }

        // Validate ISO date format YYYY-MM-DD
        $parsedDate = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            $errors['date'] = 'Ungültiges Datum.';
        }

        if (!in_array($reminderOffset, Appointment::OFFSETS, true)) {
            $errors['reminder_offset'] = 'Ungültiger Erinnerungszeitpunkt.';
        }

        if (!in_array($recurrence, Appointment::RECURRENCES, true)) {
            $errors['recurrence'] = 'Ungültige Wiederholung.';
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return compact('title', 'date', 'reminderOffset', 'recurrence');
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    /**
     * GET /api/appointments
     * Return all appointments belonging to the logged-in user.
     */
    public function index(array $params = []): void
    {
        $userId       = $this->requireAuth();
        $appointments = Appointment::allForUser($userId);
        $this->json($appointments);
    }

    /**
     * POST /api/appointments
     * Create a new appointment from the JSON request body.
     */
    public function store(array $params = []): void
    {
        $userId = $this->requireAuth();
        $data   = $this->jsonBody();
        $fields = $this->validateFields($data);

        if (isset($fields['errors'])) {
            $this->json($fields, 422);
        }

        $id = Appointment::create(
            $userId,
            $fields['title'],
            $fields['date'],
            $fields['reminderOffset'],
            $fields['recurrence']
        );

        // Return the newly created record so the client can append it to the list
        $appointment = Appointment::findForUser($id, $userId);
        $this->json($appointment, 201);
    }

    /**
     * PUT /api/appointments/:id
     * Update an existing appointment (must belong to the logged-in user).
     */
    public function update(array $params = []): void
    {
        $userId = $this->requireAuth();
        $id     = (int) ($params['id'] ?? 0);
        $data   = $this->jsonBody();

        // Ensure the appointment exists and belongs to this user
        if (Appointment::findForUser($id, $userId) === null) {
            $this->json(['error' => 'Termin nicht gefunden.'], 404);
        }

        $fields = $this->validateFields($data);
        if (isset($fields['errors'])) {
            $this->json($fields, 422);
        }

        Appointment::update(
            $id,
            $userId,
            $fields['title'],
            $fields['date'],
            $fields['reminderOffset'],
            $fields['recurrence']
        );

        $this->json(Appointment::findForUser($id, $userId));
    }

    /**
     * DELETE /api/appointments/:id
     * Delete an appointment (must belong to the logged-in user).
     */
    public function destroy(array $params = []): void
    {
        $userId = $this->requireAuth();
        $id     = (int) ($params['id'] ?? 0);

        if (!Appointment::delete($id, $userId)) {
            $this->json(['error' => 'Termin nicht gefunden.'], 404);
        }

        // 204 No Content is the standard response for a successful DELETE
        http_response_code(204);
        exit;
    }
}
