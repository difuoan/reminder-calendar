<!--
    Login page partial – included by layout.php
    Alpine.js drives form submission and field-error animations.
-->

<?php
// Server-side errors passed from AuthController (e.g. wrong credentials)
$errors ??= [];
?>

<div class="max-w-md mx-auto animate-fadeInUp">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-1">Anmelden</h2>
        <p class="text-slate-500 text-sm mb-6">
            Noch kein Konto?
            <a href="/register" class="text-teal-600 hover:underline font-medium">Jetzt registrieren</a>
        </p>

        <?php if (!empty($errors['general'])): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            <?= htmlspecialchars($errors['general']) ?>
        </div>
        <?php endif ?>

        <form method="POST" action="/login" x-data="loginForm()">

            <!-- E-Mail -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="email">E-Mail</label>
                <input id="email" name="email" type="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email"
                       :class="errors.email ? 'field-error' : ''"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                              transition-colors">
                <p x-show="errors.email" x-text="errors.email"
                   class="text-red-500 text-xs mt-1"></p>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Passwort</label>
                <input id="password" name="password" type="password"
                       required autocomplete="current-password"
                       :class="errors.password ? 'field-error' : ''"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                              transition-colors">
                <p x-show="errors.password" x-text="errors.password"
                   class="text-red-500 text-xs mt-1"></p>
            </div>

            <button type="submit"
                    :disabled="loading"
                    class="w-full py-2.5 bg-teal-600 text-white font-semibold rounded-lg
                           hover:bg-teal-500 transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                <svg x-show="loading" style="display:none" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span x-text="loading ? 'Anmelden…' : 'Anmelden'">Anmelden</span>
            </button>
        </form>
    </div>
</div>

<script>
/**
 * Alpine component for the login form.
 * Handles client-side presence validation and a loading state
 * while the form is being submitted.
 */
function loginForm() {
    return {
        loading: false,
        errors: {
            email:    '<?= addslashes($errors['email']    ?? '') ?>',
            password: '<?= addslashes($errors['password'] ?? '') ?>',
        },
    };
}
</script>
