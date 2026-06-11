<?php
/**
 * Main layout template
 *
 * Every page is rendered inside this shell.
 * Controllers set $pageTitle before including this file.
 * The correct content partial is chosen based on $pageTitle
 * to keep routing logic out of the views.
 */

$currentUser = $_SESSION['user_name'] ?? null;
$currentPath = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Kalender') ?> – Reminder Calendar</title>

    <!-- Tailwind CSS via CDN (no build step required) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js via CDN – deferred so it runs after the DOM is ready -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>

    <!-- Custom styles: animations, transitions, and overrides not covered by Tailwind utilities -->
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

<!-- ============================================================
     Top navigation bar
     ============================================================ -->
<header class="bg-slate-800 shadow-md sticky top-0 z-50 border-b border-teal-500/20">
    <nav class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">

        <!-- Logo / Brand -->
        <a href="/" class="flex items-center gap-2 text-white font-bold text-lg tracking-tight hover:text-teal-400 transition-colors">
            <svg class="w-7 h-7 text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Reminder
        </a>

        <!-- Primary navigation links (left side) -->
        <ul class="hidden sm:flex items-center gap-1 ml-6">
            <?php
            $navLinks = [
                '/'         => 'Home',
                '/calendar' => 'Kalender',
            ];
            foreach ($navLinks as $href => $label):
                $isActive = ($currentPath === $href || ($href !== '/' && str_starts_with($currentPath, $href)));
            ?>
            <li>
                <a href="<?= $href ?>"
                   class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                          <?= $isActive
                              ? 'bg-teal-600 text-white'
                              : 'text-slate-300 hover:text-white hover:bg-slate-700' ?>">
                    <?= $label ?>
                </a>
            </li>
            <?php endforeach ?>
        </ul>

        <!-- Auth buttons (right side) -->
        <div class="flex items-center gap-3 ml-auto">
            <?php if ($currentUser): ?>
                <span class="text-slate-400 text-sm hidden sm:inline">
                    Hallo, <span class="text-white font-medium"><?= htmlspecialchars($currentUser) ?></span>
                </span>
                <a href="/logout"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium bg-slate-700 text-slate-200
                          hover:bg-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Abmelden
                </a>
            <?php else: ?>
                <a href="/login"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-colors
                          <?= $currentPath === '/login'
                              ? 'bg-slate-700 text-white'
                              : 'text-slate-300 hover:text-white hover:bg-slate-700' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Anmelden
                </a>
                <a href="/register"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition-colors
                          <?= $currentPath === '/register'
                              ? 'bg-teal-600 text-white'
                              : 'bg-teal-600 text-white hover:bg-teal-500' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Registrieren
                </a>
            <?php endif ?>
        </div>

    </nav>
</header>

<!-- ============================================================
     Main content – partial selected by page title
     ============================================================ -->
<main class="flex-1 max-w-6xl w-full mx-auto px-4 py-10">
    <?php
    // Map page titles to their view partials
    $view = match ($pageTitle ?? '') {
        'Erinnerungskalender' => __DIR__ . '/calendar.php',
        'Anmelden'            => __DIR__ . '/login.php',
        'Registrieren'        => __DIR__ . '/register.php',
        default               => __DIR__ . '/home.php',
    };
    require $view;
    ?>
</main>

<!-- ============================================================
     Footer
     ============================================================ -->
<footer class="bg-slate-800 text-slate-500 text-center text-xs py-4 mt-auto">
    &copy; <?= date('Y') ?> Reminder Calendar
</footer>

</body>
</html>
