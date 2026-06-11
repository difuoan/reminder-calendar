<?php
/**
 * ReminderLog model
 *
 * Tracks which reminder emails have already been sent for each
 * appointment occurrence, preventing duplicate sends.
 */

declare(strict_types=1);

namespace App\Models;

use App\Database;

class ReminderLog
{
    /**
     * Check whether a reminder has already been sent for a given
     * appointment and occurrence date.
     *
     * @param  int    $appointmentId
     * @param  string $occurrenceDate ISO date YYYY-MM-DD
     * @return bool
     */
    public static function exists(int $appointmentId, string $occurrenceDate): bool
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT 1 FROM reminder_logs
              WHERE appointment_id = ? AND occurrence_date = ?
              LIMIT 1'
        );
        $stmt->execute([$appointmentId, $occurrenceDate]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Record a sent reminder so it is not dispatched again.
     *
     * @param  int    $appointmentId
     * @param  string $occurrenceDate ISO date YYYY-MM-DD
     */
    public static function record(int $appointmentId, string $occurrenceDate): void
    {
        $stmt = Database::getInstance()->prepare(
            'INSERT IGNORE INTO reminder_logs (appointment_id, occurrence_date)
             VALUES (?, ?)'
        );
        $stmt->execute([$appointmentId, $occurrenceDate]);
    }
}
