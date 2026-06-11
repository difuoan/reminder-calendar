-- ============================================================
-- Migration 004: Create reminder_logs table
--
-- Records every reminder email that has been sent, keyed on
-- the appointment ID and the specific occurrence date.
--
-- This prevents the daily cron script from sending duplicate
-- emails if it runs multiple times on the same day, and also
-- provides a simple audit trail of sent notifications.
-- ============================================================

CREATE TABLE IF NOT EXISTS reminder_logs (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    appointment_id  INT UNSIGNED    NOT NULL                COMMENT 'The appointment this reminder was sent for',
    occurrence_date DATE            NOT NULL                COMMENT 'The event date this reminder relates to',
    sent_at         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Prevent duplicate sends for the same appointment + occurrence
    UNIQUE KEY uq_reminder_logs (appointment_id, occurrence_date),
    CONSTRAINT fk_reminder_logs_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
