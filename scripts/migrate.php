<?php
/**
 * ============================================================
 * Database migration runner
 * ============================================================
 *
 * PURPOSE
 *   Applies unapplied SQL migration files in numeric order so that
 *   anyone can set up the database schema with a single command.
 *
 * USAGE
 *   docker exec calendar_app php /var/www/html/scripts/migrate.php
 *
 *   Or locally (if PHP is on the PATH):
 *   php scripts/migrate.php
 *
 * HOW IT WORKS
 *   1. Ensures the `migrations` tracking table exists (001_*.sql)
 *   2. Reads all *.sql files from database/migrations/ in name order
 *   3. Skips files already recorded in the migrations table
 *   4. Executes new files and records them – safe to re-run at any time
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Database;

$pdo            = Database::getInstance();
$migrationsDir  = __DIR__ . '/../database/migrations';
$appliedCount   = 0;
$skippedCount   = 0;

echo "=== Running database migrations ===\n\n";

// ── Step 1: Bootstrap the migrations tracking table ──────────────────────────
// We manually execute the first migration file before relying on the table,
// since the table doesn't exist yet on a fresh database.
$bootstrapFile = $migrationsDir . '/001_create_migrations_table.sql';
if (!file_exists($bootstrapFile)) {
    exit("ERROR: Bootstrap migration file not found: {$bootstrapFile}\n");
}
$pdo->exec(file_get_contents($bootstrapFile));

// ── Step 2: Collect all migration files sorted by filename ───────────────────
$files = glob($migrationsDir . '/*.sql');
if ($files === false || count($files) === 0) {
    exit("No migration files found in {$migrationsDir}\n");
}
sort($files); // Alphabetical sort = numeric sort for 001_, 002_, … naming

// ── Step 3: Load the set of already-applied migrations ───────────────────────
$alreadyApplied = $pdo
    ->query('SELECT filename FROM migrations')
    ->fetchAll(PDO::FETCH_COLUMN);
$alreadyApplied = array_flip($alreadyApplied); // flip for O(1) isset() lookup

// ── Step 4: Apply unapplied files ────────────────────────────────────────────
foreach ($files as $filePath) {
    $filename = basename($filePath);

    if (isset($alreadyApplied[$filename])) {
        echo "  SKIP  {$filename}\n";
        $skippedCount++;
        continue;
    }

    echo "  APPLY {$filename} ... ";

    try {
        $sql = file_get_contents($filePath);
        $pdo->exec($sql);

        // Record successful application so this file is never run again
        $stmt = $pdo->prepare('INSERT IGNORE INTO migrations (filename) VALUES (?)');
        $stmt->execute([$filename]);

        echo "OK\n";
        $appliedCount++;

    } catch (\PDOException $e) {
        // Abort on any error – a partial migration leaves the schema in an
        // inconsistent state and must be investigated before continuing.
        echo "FAILED\n";
        echo "\nERROR in {$filename}:\n  " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\nDone. Applied: {$appliedCount}, Skipped: {$skippedCount}\n";
