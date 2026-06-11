<!--
    Register page partial – included by layout.php
-->

<?php $errors ??= []; ?>

<div class="max-w-md mx-auto animate-fadeInUp">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-1">Konto erstellen</h2>
        <p class="text-slate-500 text-sm mb-6">
            Bereits registriert?
            <a href="/login" class="text-teal-600 hover:underline font-medium">Jetzt anmelden</a>
        </p>

        <form method="POST" action="/register" x-data="registerForm()">

            <!-- Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="name">Name</label>
                <input id="name" name="name" type="text"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       required autocomplete="name"
                       :class="serverErrors.name ? 'field-error' : ''"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                              transition-colors">
                <p x-show="serverErrors.name" x-text="serverErrors.name"
                   class="text-red-500 text-xs mt-1"></p>
            </div>

            <!-- E-Mail -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="email">E-Mail</label>
                <input id="email" name="email" type="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email"
                       :class="serverErrors.email ? 'field-error' : ''"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                              transition-colors">
                <p x-show="serverErrors.email" x-text="serverErrors.email"
                   class="text-red-500 text-xs mt-1"></p>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Passwort</label>
                <input id="password" name="password" type="password"
                       required autocomplete="new-password"
                       :class="serverErrors.password ? 'field-error' : ''"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                              transition-colors">
                <p class="text-slate-400 text-xs mt-1">Mindestens 8 Zeichen.</p>
                <p x-show="serverErrors.password" x-text="serverErrors.password"
                   class="text-red-500 text-xs mt-1"></p>
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-teal-600 text-white font-semibold rounded-lg
                           hover:bg-teal-500 transition-colors inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Konto erstellen
            </button>
        </form>
    </div>
</div>

<script>
function registerForm() {
    return {
        serverErrors: {
            name:     '<?= addslashes($errors['name']     ?? '') ?>',
            email:    '<?= addslashes($errors['email']    ?? '') ?>',
            password: '<?= addslashes($errors['password'] ?? '') ?>',
        },
    };
}
</script>
