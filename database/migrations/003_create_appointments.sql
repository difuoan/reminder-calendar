-- ============================================================
-- Migration 003: Create appointments table
--
-- Each row is one reminder appointment belonging to a user.
--
-- date            – The first (or only) occurrence of the event.
-- reminder_offset – How many days before the event date the
--                   reminder email should be sent.
-- recurrence      – Controls how the reminder script calculates
--                   the next occurrence after each send cycle.
--                   'one_time' appointments are never re-armed.
-- ============================================================

CREATE TABLE IF NOT EXISTS appointments (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED    NOT NULL                        COMMENT 'Owner – references users.id',
    title           VARCHAR(255)    NOT NULL                        COMMENT 'Human-readable event name, e.g. "Hochzeitstag"',
    date            DATE            NOT NULL                        COMMENT 'First (or only) occurrence of the event',
    reminder_offset ENUM(
        '1_day',
        '2_days',
        '4_days',
        '1_week',
        '2_weeks'
    )               NOT NULL DEFAULT '1_day'                       COMMENT 'How far in advance to send the reminder',
    recurrence      ENUM(
        'one_time',
        'daily',
        'weekly',
        'monthly',
        'yearly'
    )               NOT NULL DEFAULT 'one_time'                    COMMENT 'Recurrence cadence; one_time events are never re-armed',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_appointments_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
