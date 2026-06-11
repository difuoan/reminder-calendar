<?php
/**
 * Appointment model
 *
 * Encapsulates all database operations for appointment records.
 * All write methods are scoped to a specific user_id to prevent
 * one user from accessing or modifying another user's data.
 */

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

class Appointment
{
    /** Valid values for the reminder_offset column */
    public const OFFSETS = ['1_day', '2_days', '4_days', '1_week', '2_weeks'];

    /** Mapping from offset enum value to number of days */
    public const OFFSET_DAYS = [
        '1_day'   => 1,
        '2_days'  => 2,
        '4_days'  => 4,
        '1_week'  => 7,
        '2_weeks' => 14,
    ];

    /** Valid values for the recurrence column */
    public const RECURRENCES = ['one_time', 'daily', 'weekly', 'monthly', 'yearly'];

    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * Retrieve all appointments for a given user, ordered by date.
     *
     * @param  int   $userId
     * @return array List of associative row arrays
     */
    public static function allForUser(int $userId): array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT id, title, date, reminder_offset, recurrence, created_at
               FROM appointments
              WHERE user_id = ?
           ORDER BY DATE_FORMAT(date, "%m-%d"), date'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single appointment, ensuring it belongs to the given user.
     *
     * @param  int        $id
     * @param  int        $userId
     * @return array|null Row or null if not found / not owned by user
     */
    public static function findForUser(int $id, int $userId): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT id, title, date, reminder_offset, recurrence
               FROM appointments
              WHERE id = ? AND user_id = ?
              LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch all appointments across all users, including the owner's email.
     * Used exclusively by the reminder send script.
     *
     * @return array List of rows with an extra 'email' column
     */
    public static function allWithUserEmail(): array
    {
        return Database::getInstance()
            ->query(
                'SELECT a.id, a.title, a.date, a.reminder_offset, a.recurrence,
                        u.email, u.name AS user_name
                   FROM appointments a
                   JOIN users u ON u.id = a.user_id'
            )
            ->fetchAll();
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /**
     * Insert a new appointment.
     *
     * @param  int    $userId
     * @param  string $title
     * @param  string $date           ISO date string YYYY-MM-DD
     * @param  string $reminderOffset One of self::OFFSETS
     * @param  string $recurrence     One of self::RECURRENCES
     * @return int                    New appointment ID
     */
    public static function create(
        int    $userId,
        string $title,
        string $date,
        string $reminderOffset,
        string $recurrence
    ): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO appointments (user_id, title, date, reminder_offset, recurrence)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $date, $reminderOffset, $recurrence]);
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update an existing appointment (scoped to the owning user).
     *
     * @param  int    $id
     * @param  int    $userId
     * @param  string $title
     * @param  string $date
     * @param  string $reminderOffset
     * @param  string $recurrence
     * @return bool   True if a row was actually updated
     */
    public static function update(
        int    $id,
        int    $userId,
        string $title,
        string $date,
        string $reminderOffset,
        string $recurrence
    ): bool {
        $stmt = Database::getInstance()->prepare(
            'UPDATE appointments
                SET title = ?, date = ?, reminder_offset = ?, recurrence = ?
              WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$title, $date, $reminderOffset, $recurrence, $id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an appointment (scoped to the owning user).
     *
     * @param  int  $id
     * @param  int  $userId
     * @return bool True if a row was deleted
     */
    public static function delete(int $id, int $userId): bool
    {
        $stmt = Database::getInstance()->prepare(
            'DELETE FROM appointments WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }
}
