<?php
/**
 * ============================================================
 * Reminder send script
 * ============================================================
 *
 * PURPOSE
 *   Checks every appointment in the database and sends a reminder
 *   email to the owner if today is the calculated reminder date
 *   (event date minus the chosen offset).
 *
 * USAGE
 *   Run this script once per day via cron or Windows Task Scheduler:
 *
 *   Linux/macOS cron (08:00 every day):
 *     0 8 * * * docker exec calendar_app php /var/www/html/scripts/send_reminders.php
 *
 *   Windows Task Scheduler:
 *     Program : docker
 *     Arguments: exec calendar_app php /var/www/html/scripts/send_reminders.php
 *
 * HOW DUPLICATE SENDS ARE PREVENTED
 *   After each successful send the occurrence date is written to the
 *   `reminder_logs` table (appointment_id + occurrence_date UNIQUE).
 *   On subsequent runs, already-logged combinations are skipped.
 *
 * RECURRING EVENTS
 *   For recurring appointments the script computes the NEXT occurrence
 *   on or after today, then checks whether today matches the reminder
 *   date for that occurrence. This means no extra state is needed —
 *   the script self-heals on every run.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Models\Appointment;
use App\Models\ReminderLog;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

$today    = new DateTimeImmutable('today');
$sentCount = 0;
$skipCount = 0;

echo "[" . $today->format('Y-m-d') . "] Starting reminder check...\n";

// ── Load all appointments (with owner email) ──────────────────────────────────
$appointments = Appointment::allWithUserEmail();
echo "  Found " . count($appointments) . " appointment(s) to evaluate.\n";

foreach ($appointments as $appt) {
    $eventDate  = new DateTimeImmutable($appt['date']);
    $offsetDays = Appointment::OFFSET_DAYS[$appt['reminder_offset']];

    // ── Step 1: Compute the next upcoming occurrence date ─────────────────────
    $nextOccurrence = computeNextOccurrence($eventDate, $appt['recurrence'], $today);

    if ($nextOccurrence === null) {
        // One-time event already in the past – nothing to do
        $skipCount++;
        continue;
    }

    // ── Step 2: Calculate the date on which the reminder should be sent ───────
    $reminderDate = $nextOccurrence->modify("-{$offsetDays} days");

    if ($reminderDate->format('Y-m-d') !== $today->format('Y-m-d')) {
        // Today is not the reminder day for this occurrence
        $skipCount++;
        continue;
    }

    $occurrenceDateStr = $nextOccurrence->format('Y-m-d');

    // ── Step 3: Check whether this reminder has already been sent ─────────────
    if (ReminderLog::exists((int) $appt['id'], $occurrenceDateStr)) {
        echo "  SKIP  [{$appt['title']}] reminder already sent for {$occurrenceDateStr}\n";
        $skipCount++;
        continue;
    }

    // ── Step 4: Send the reminder email via PHPMailer + SMTP ─────────────────
    $sent = sendReminderEmail($appt, $nextOccurrence, $offsetDays);

    if ($sent) {
        // Record the send so we never dispatch this reminder twice
        ReminderLog::record((int) $appt['id'], $occurrenceDateStr);
        echo "  SENT  [{$appt['title']}] → {$appt['email']} (event: {$occurrenceDateStr})\n";
        $sentCount++;
    }
}

echo "Done. Sent: {$sentCount}, Skipped: {$skipCount}\n";


// ── Helper functions ──────────────────────────────────────────────────────────

/**
 * Compute the next occurrence of an event on or after today.
 *
 * For 'one_time' events the original date is returned only if it is
 * in the future (or today); past one-time events return null.
 *
 * @param  DateTimeImmutable  $eventDate   First/original occurrence date
 * @param  string             $recurrence  Recurrence enum value
 * @param  DateTimeImmutable  $today
 * @return DateTimeImmutable|null
 */
function computeNextOccurrence(
    DateTimeImmutable $eventDate,
    string $recurrence,
    DateTimeImmutable $today
): ?DateTimeImmutable {

    if ($recurrence === 'one_time') {
        // Only relevant if the event hasn't passed yet
        return $eventDate >= $today ? $eventDate : null;
    }

    // For recurring events, advance the start date by the recurrence interval
    // until we reach a date on or after today.
    $occurrence = $eventDate;
    $maxSteps   = 3650; // Safety cap – prevents infinite loops for edge cases

    for ($i = 0; $i < $maxSteps; $i++) {
        if ($occurrence >= $today) {
            return $occurrence;
        }

        // Advance by one recurrence period
        $occurrence = match ($recurrence) {
            'daily'   => $occurrence->modify('+1 day'),
            'weekly'  => $occurrence->modify('+1 week'),
            'monthly' => $occurrence->modify('+1 month'),
            'yearly'  => $occurrence->modify('+1 year'),
            default   => null,
        };

        if ($occurrence === null) {
            break;
        }
    }

    return null;
}

/**
 * Compose and send a reminder email using PHPMailer over SMTP.
 *
 * In development, SMTP points to Mailpit (localhost:1025) which
 * captures the email without sending it for real.
 *
 * @param  array              $appt        Appointment row with user email/name
 * @param  DateTimeImmutable  $occurrence  The event date being reminded about
 * @param  int                $offsetDays  Days before the event
 * @return bool                            True on success
 */
function sendReminderEmail(array $appt, DateTimeImmutable $occurrence, int $offsetDays): bool
{
    $mail = new PHPMailer(true);

    try {
        // ── SMTP configuration ────────────────────────────────────────────────
        $mail->isSMTP();
        $mail->Host    = $_ENV['MAIL_HOST'] ?? 'mail';
        $mail->Port    = (int) ($_ENV['MAIL_PORT'] ?? 1025);
        $mail->CharSet = 'UTF-8';

        // Enable authentication when credentials are provided
        $mailUser = $_ENV['MAIL_USER'] ?? '';
        $mailPass = $_ENV['MAIL_PASS'] ?? '';
        if ($mailUser !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $mailUser;
            $mail->Password = $mailPass;
        } else {
            $mail->SMTPAuth = false; // local dev (Mailpit)
        }

        // TLS/SSL encryption (tls = STARTTLS on 587, ssl = SMTPS on 465, empty = none)
        $encryption = strtolower($_ENV['MAIL_ENCRYPTION'] ?? '');
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        // ── Sender and recipient ──────────────────────────────────────────────
        $mail->setFrom(
            $_ENV['MAIL_FROM']      ?? 'noreply@calendar.local',
            $_ENV['MAIL_FROM_NAME'] ?? 'Reminder Calendar'
        );
        $mail->addAddress($appt['email'], $appt['user_name']);

        // ── Email content ─────────────────────────────────────────────────────
        $eventFormatted = $occurrence->format('d.m.Y');
        $daysWord       = $offsetDays === 1 ? 'morgen' : "in {$offsetDays} Tagen";

        $mail->Subject = "Erinnerung: {$appt['title']} am {$eventFormatted}";

        $mail->isHTML(true);
        $mail->Body = <<<HTML
            <div style="font-family: sans-serif; max-width: 500px; margin: 0 auto;">
                <h2 style="color: #0d9488;">⏰ Terminerinnerung</h2>
                <p>Hallo {$appt['user_name']},</p>
                <p>dies ist eine Erinnerung für folgenden Termin:</p>
                <table style="border-collapse: collapse; width: 100%; margin: 16px 0;">
                    <tr>
                        <td style="padding: 8px; font-weight: bold;">Bezeichnung</td>
                        <td style="padding: 8px;">{$appt['title']}</td>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td style="padding: 8px; font-weight: bold;">Datum</td>
                        <td style="padding: 8px;">{$eventFormatted}</td>
                    </tr>
                </table>
                <p>Der Termin findet <strong>{$daysWord}</strong> statt.</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
                <p style="font-size: 12px; color: #94a3b8;">
                    Diese E-Mail wurde automatisch von Reminder Calendar versandt.
                </p>
            </div>
        HTML;

        $mail->AltBody = "Erinnerung: {$appt['title']} findet am {$eventFormatted} ({$daysWord}) statt.";

        $mail->send();
        return true;

    } catch (MailerException $e) {
        echo "  ERROR Could not send to {$appt['email']}: {$mail->ErrorInfo}\n";
        return false;
    }
}
