# Reminder Calendar

A personal reminder calendar web application built as a PHP programming exercise.
Users can register, log in, and manage appointment reminders — the app sends an
email notification a configurable number of days before each event.

## Tech Stack

| Layer       | Technology                                     |
| ----------- | ---------------------------------------------- |
| Backend     | PHP 8.2 · plain MVC (no framework) · PDO       |
| Database    | MySQL 8                                        |
| Frontend    | Alpine.js 3 (via CDN) · Tailwind CSS (via CDN) |
| Email       | PHPMailer · Mailpit (local dev inbox)          |
| Environment | Docker · Docker Compose                        |

## Features

- **Registration & Login** — name, email, password (bcrypt); session-based auth
- **Appointment CRUD** — create, edit and delete reminders without page reloads (AJAX / JSON API)
- **Reminder offsets** — 1 day · 2 days · 4 days · 1 week · 2 weeks before the event
- **Recurrence** — one-time · daily · weekly · monthly · yearly
- **Automated reminders** — CLI script checks due reminders and sends personalised emails
- **Modern UI** — toggle-able appointment form (hidden until needed), animated validation, row transitions, toast notifications, icon-enhanced buttons

---

## Quick Start

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running

### 1. Clone / unzip the project

```bash
cd path/to/microlab
```

### 2. Configure environment

```bash
cp .env.example .env
```

The defaults work out of the box with Docker Compose — no changes needed for local development.

### 3. Start the containers

```bash
docker compose up -d
```

This starts three containers:

| Container       | Purpose                 | Local URL                                                   |
| --------------- | ----------------------- | ----------------------------------------------------------- |
| `calendar_app`  | PHP 8.2 + Nginx         | http://localhost:8080                                       |
| `calendar_db`   | MySQL 8                 | `localhost:3306` (TCP only — use phpMyAdmin or a DB client) |
| `calendar_mail` | Mailpit (email testing) | http://localhost:8025                                       |
| `calendar_pma`  | phpMyAdmin              | http://localhost:8081                                       |

### 4. Run database migrations

```bash
docker exec calendar_app php /var/www/html/scripts/migrate.php
```

### 5. Open the app

Navigate to **http://localhost:8080** in your browser.

**Dev tooling URLs:**

| Tool        | URL                   |
| ----------- | --------------------- |
| Application | http://localhost:8080 |
| phpMyAdmin  | http://localhost:8081 |
| Mailpit     | http://localhost:8025 |

---

## Sending Reminder Emails (manually)

The reminder script is designed to run once per day. Run it manually with:

```bash
docker exec calendar_app php /var/www/html/scripts/send_reminders.php
```

All sent emails are captured by Mailpit and visible at **http://localhost:8025** — nothing is delivered to a real inbox during development.

### Scheduling (production)

**Linux/macOS cron** – runs daily at 08:00:

```
0 8 * * * docker exec calendar_app php /var/www/html/scripts/send_reminders.php
```

**Windows Task Scheduler:**

- Program: `docker`
- Arguments: `exec calendar_app php /var/www/html/scripts/send_reminders.php`
- Trigger: Daily at 08:00

---

## Project Structure

```
├── docker/
│   ├── nginx/default.conf      Nginx virtual-host config
│   └── php/
│       ├── Dockerfile           PHP 8.2-FPM + Nginx image
│       └── entrypoint.sh        Container startup script
├── database/
│   └── migrations/             Numbered SQL migration files
├── public/
│   ├── index.php               Front controller (single entry point)
│   └── assets/                 CSS, JS, images
├── scripts/
│   ├── migrate.php             CLI migration runner
│   └── send_reminders.php      CLI reminder send script
├── src/
│   ├── bootstrap.php           Autoloader, .env parser, session start
│   ├── Database.php            PDO singleton factory
│   ├── Router.php              URL router
│   ├── Controllers/            HomeController, AuthController,
│   │                           AppointmentController, ApiController
│   ├── Models/                 User, Appointment, ReminderLog
│   ├── Middleware/             AuthMiddleware
│   └── Views/                  PHP/HTML templates
├── .env.example                Environment variable template (copy to .env)
├── composer.json               PHP dependencies (PHPMailer)
└── docker-compose.yml          Docker service definitions (app · db · mail · phpmyadmin)
```

---

## Database Schema

```
migrations        – tracks applied migration files
users             – registered user accounts
appointments      – reminder events (linked to user)
reminder_logs     – audit log of sent reminders (prevents duplicate sends)
```

Inspect the migration files in `database/migrations/` for the full column definitions and comments.

---

## Stopping the Application

```bash
docker compose down          # stop containers, keep database volume
docker compose down -v       # stop containers AND delete the database
```

---

## Back-comparison with the Original Brief

The original task (`instructions/microlab_programmieraufgabe.pdf`) showed two mockup screenshots of a simple IE-era PHP app. Below is a point-by-point comparison.

### Core requirements — all met

| Original requirement                                                              | Our implementation                                                   |
| --------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| Home page with navigation and lorem-ipsum content                                 | ✅ `/` renders `home.php` with hero section and lorem-ipsum text     |
| Calendar page accessible via navigation                                           | ✅ `/calendar` (protected, requires login)                           |
| Add appointment: date (DD/MM), title (_Bezeichnung_), reminder lead-time dropdown | ✅ Inline form with date picker, title input, reminder-offset select |
| List all appointments (Datum DD.MM., Bezeichnung, Erinnerung, Aktion)             | ✅ Sortable table with identical German column headers               |
| Edit appointment (_bearbeiten_)                                                   | ✅ Inline row editing without page reload                            |
| Delete appointment (_löschen_)                                                    | ✅ Delete with animated row removal                                  |
| Reminder email send script                                                        | ✅ `scripts/send_reminders.php` sends HTML email via PHPMailer       |
| Date display in DD.MM. format                                                     | ✅ `formatDate()` in Alpine component renders `28.05.`               |

### Additions beyond the original brief

| Addition                                              | Rationale                                                                                                          |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| **User registration & login**                         | Allows multi-user deployment; scopes data per user; follows security best practices (bcrypt, session fixation fix) |
| **Recurrence options** (daily/weekly/monthly/yearly)  | Obvious UX improvement for recurring events like birthdays                                                         |
| **Multiple reminder offsets** (1 day to 2 weeks)      | Gives users more flexibility; requested implicitly by the reminder concept                                         |
| **Duplicate-send prevention** (`reminder_logs` table) | Prevents sending the same reminder email twice if the script runs more than once per day                           |
| **Docker + Nginx + PHP-FPM**                          | Reproducible environment; no manual PHP/MySQL setup; matches modern professional practice                          |
| **Alpine.js reactive UI**                             | No-reload CRUD; toggle-able form panel; animated feedback; toast notifications; icon-enhanced buttons              |
| **Tailwind CSS**                                      | Consistent, responsive design system                                                                               |
| **Mailpit email sandbox**                             | Safe email testing without real SMTP credentials                                                                   |
| **phpMyAdmin**                                        | Browser-based DB inspection at http://localhost:8081 — no separate DB client needed                                |
| **Idempotent migration runner**                       | Safe to re-run; tracks applied migrations                                                                          |

### Known intentional deviations

- **Year stored internally** — the original only showed day+month, but storing the full date (YYYY-MM-DD) is necessary for the reminder offset calculation to work correctly across year boundaries. The display still shows `DD.MM.` as in the mockup.
- **Authentication required** — the original showed no login screen; we added one because storing personal reminder data without auth would be a security gap in any real deployment.
