<!--
    Calendar page partial – included by layout.php
    ================================================================
    This page is driven by a single Alpine.js component (`calendarApp`)
    defined at the bottom of this file.

    Flow:
      1. On mount (`init()`), fetch all appointments via GET /api/appointments
      2. The form can be in 'create' or 'edit' mode depending on whether
         the user clicked "bearbeiten" on an existing row
      3. Form submission POSTs or PUTs to the JSON API; the response
         is used to update the reactive `appointments` array directly
         (no full page reload needed)
      4. Deleted rows trigger a fade-out animation before being removed
         from the array
      5. A toast notification confirms every successful action
-->

<?php
// Label maps passed as PHP → JS to keep translations in one place
$offsetLabels = [
    '1_day'   => '1 Tag',
    '2_days'  => '2 Tage',
    '4_days'  => '4 Tage',
    '1_week'  => '1 Woche',
    '2_weeks' => '2 Wochen',
];
$recurrenceLabels = [
    'one_time' => 'Einmalig',
    'daily'    => 'Täglich',
    'weekly'   => 'Wöchentlich',
    'monthly'  => 'Monatlich',
    'yearly'   => 'Jährlich',
];
?>

<!-- Toast notification (top-right corner, auto-dismisses after 3 s) -->
<div x-data x-show="$store.toast.visible"
     x-transition:enter="toast-enter"
     x-transition:leave="toast-leave"
     :class="$store.toast.type === 'error' ? 'bg-red-600' : 'bg-teal-600'"
     class="fixed top-20 right-4 z-50 text-white text-sm font-medium px-5 py-3
            rounded-xl shadow-lg flex items-center gap-2 min-w-[220px]">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              x-bind:d="$store.toast.type === 'error'
                ? 'M6 18L18 6M6 6l12 12'
                : 'M5 13l4 4L19 7'"/>
    </svg>
    <span x-text="$store.toast.message"></span>
</div>

<!-- Main calendar component -->
<div x-data="calendarApp()" x-init="init()" class="space-y-8 animate-fadeInUp">

    <!-- ── Form card (hidden until + or edit is clicked) ────────────────── -->
    <div x-show="formOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-slate-900"
                x-text="editingId ? 'Termin bearbeiten' : 'Neuer Termin'"></h2>
            <button type="button" @click="closeForm()"
                    class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded transition-colors"
                    title="Schließen">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm" :class="{ 'animate-shake': shakeTrigger }">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                <!-- Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                        Datum
                    </label>
                    <input type="date" x-model="form.date"
                           :class="formErrors.date ? 'field-error' : ''"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                                  transition-colors">
                    <p x-show="formErrors.date" x-text="formErrors.date"
                       class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Title -->
                <div class="sm:col-span-1 lg:col-span-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                        Bezeichnung
                    </label>
                    <input type="text" x-model="form.title" placeholder="z. B. Hochzeitstag"
                           :class="formErrors.title ? 'field-error' : ''"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm
                                  focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                                  transition-colors">
                    <p x-show="formErrors.title" x-text="formErrors.title"
                       class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Reminder offset -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                        Erinnerung
                    </label>
                    <select x-model="form.reminder_offset"
                            :class="formErrors.reminder_offset ? 'field-error' : ''"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                                   transition-colors">
                        <option value="">– bitte auswählen –</option>
                        <template x-for="[val, label] in offsetOptions" :key="val">
                            <option :value="val" x-text="label"></option>
                        </template>
                    </select>
                    <p x-show="formErrors.reminder_offset" x-text="formErrors.reminder_offset"
                       class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Recurrence -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                        Wiederholung
                    </label>
                    <select x-model="form.recurrence"
                            :class="formErrors.recurrence ? 'field-error' : ''"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                                   transition-colors">
                        <option value="">– bitte auswählen –</option>
                        <template x-for="[val, label] in recurrenceOptions" :key="val">
                            <option :value="val" x-text="label"></option>
                        </template>
                    </select>
                    <p x-show="formErrors.recurrence" x-text="formErrors.recurrence"
                       class="text-red-500 text-xs mt-1"></p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-3 mt-5">
                <button type="submit" :disabled="saving"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-teal-600 text-white
                               font-semibold rounded-lg hover:bg-teal-500 transition-colors
                               disabled:opacity-60 text-sm">
                    <!-- Spinner shown while saving -->
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <!-- Check icon when idle -->
                    <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="saving ? 'Wird gespeichert…' : (editingId ? 'Aktualisieren' : 'Speichern')"></span>
                </button>

                <!-- Cancel: visible whenever the form panel is open -->
                <button type="button" x-show="formOpen" @click="closeForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                               text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Abbrechen
                </button>
            </div>
        </form>
    </div>

    <!-- ── Appointments table ─────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Loading skeleton -->
        <div x-show="loading" class="p-8 text-center text-slate-400 text-sm">
            <svg class="w-6 h-6 animate-spin mx-auto mb-2 text-teal-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Termine werden geladen…
        </div>

        <table x-show="!loading" class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600 w-32">Datum</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600">Bezeichnung</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden sm:table-cell">Erinnerung</th>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">Wiederholung</th>
                    <th class="text-right px-5 py-3 font-semibold text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <!-- Empty state -->
                <tr x-show="appointments.length === 0 && !loading">
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                        Noch keine Termine eingetragen.
                    </td>
                </tr>

                <!-- Appointment rows – animated via CSS class toggling -->
                <template x-for="appt in appointments" :key="appt.id">
                    <tr :class="appt._leaving ? 'row-leave' : 'row-enter'"
                        class="border-b border-slate-50 hover:bg-slate-50 transition-colors">

                        <td class="px-5 py-3 tabular-nums text-slate-700"
                            x-text="formatDate(appt.date)"></td>

                        <td class="px-5 py-3 font-medium text-slate-800"
                            x-text="appt.title"></td>

                        <td class="px-5 py-3 text-slate-600 hidden sm:table-cell"
                            x-text="offsetLabel(appt.reminder_offset)"></td>

                        <td class="px-5 py-3 text-slate-600 hidden md:table-cell"
                            x-text="recurrenceLabel(appt.recurrence)"></td>

                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <button @click="startEdit(appt)"
                                    class="inline-flex items-center gap-1 text-teal-600 hover:text-teal-800 font-medium mr-3 text-xs hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                bearbeiten
                            </button>
                            <button @click="deleteAppt(appt)"
                                    class="inline-flex items-center gap-1 text-red-400 hover:text-red-600 font-medium text-xs hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                löschen
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
            </table>
            <!-- ── "+" add row ─────────────────────────────────────── -->
            <div x-show="!loading" class="border-t border-slate-100">
                <button @click="openNewForm()"
                        class="w-full flex items-center justify-center gap-2 py-3 text-sm font-medium
                               text-teal-600 hover:bg-teal-50 transition-colors rounded-b-2xl">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Neuer Termin
                </button>
            </div>
        </div>
    </div>

<script>
/* ====================================================================
 * Alpine global store – toast notification singleton
 *
 * Any component can trigger a toast with:
 *   Alpine.store('toast').show('Gespeichert!', 'success')
 *   Alpine.store('toast').show('Fehler aufgetreten.', 'error')
 * ==================================================================== */
document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        visible: false,
        message: '',
        type: 'success',
        _timer: null,

        /**
         * Display a toast for 3 seconds then auto-hide.
         * @param {string} message - Text to display
         * @param {'success'|'error'} type - Controls background colour
         */
        show(message, type = 'success') {
            clearTimeout(this._timer);
            this.message = message;
            this.type    = type;
            this.visible = true;
            this._timer  = setTimeout(() => { this.visible = false; }, 3000);
        },
    });
});

/* ====================================================================
 * calendarApp – main Alpine component for the appointments page
 * ==================================================================== */
function calendarApp() {
    return {
        // ── State ──────────────────────────────────────────────────────

        /** Full list of the user's appointments fetched from the API */
        appointments: [],

        /** Whether the initial GET request is in-flight */
        loading: true,

        /** Whether the form POST/PUT request is in-flight */
        saving: false,

        /** ID of the appointment currently being edited, or null for create mode */
        editingId: null,

        /** Triggers the CSS shake animation on invalid submit */
        shakeTrigger: false,

        /** Whether the add/edit form panel is visible */
        formOpen: false,

        /** Current form field values (two-way bound via x-model) */
        form: {
            title:           '',
            date:            '',
            reminder_offset: '',
            recurrence:      '',
        },

        /** Server-side or client-side validation errors per field */
        formErrors: {},

        // Dropdown option arrays populated from PHP-rendered JSON
        offsetOptions:     <?= json_encode(array_map(null, array_keys($offsetLabels),    array_values($offsetLabels)),    JSON_UNESCAPED_UNICODE) ?>,
        recurrenceOptions: <?= json_encode(array_map(null, array_keys($recurrenceLabels), array_values($recurrenceLabels)), JSON_UNESCAPED_UNICODE) ?>,

        // Label lookup maps for rendering table cells
        _offsetMap:     <?= json_encode($offsetLabels,    JSON_UNESCAPED_UNICODE) ?>,
        _recurrenceMap: <?= json_encode($recurrenceLabels, JSON_UNESCAPED_UNICODE) ?>,

        // ── Lifecycle ──────────────────────────────────────────────────

        /** Called by x-init – load appointments on page mount */
        async init() {
            await this.fetchAppointments();
        },

        // ── Data fetching ──────────────────────────────────────────────

        /** Load all appointments from the API and populate the list */
        async fetchAppointments() {
            this.loading = true;
            try {
                const res = await fetch('/api/appointments');
                this.appointments = await res.json();
            } catch (e) {
                Alpine.store('toast').show('Termine konnten nicht geladen werden.', 'error');
            } finally {
                this.loading = false;
            }
        },

        // ── Form helpers ───────────────────────────────────────────────

        /**
         * Client-side validation before sending to the API.
         * Returns true if the form is valid, false otherwise.
         * Triggers a shake animation on the form when invalid.
         */
        validateForm() {
            this.formErrors = {};

            if (!this.form.title.trim()) {
                this.formErrors.title = 'Bezeichnung ist erforderlich.';
            }
            if (!this.form.date) {
                this.formErrors.date = 'Datum ist erforderlich.';
            }
            if (!this.form.reminder_offset) {
                this.formErrors.reminder_offset = 'Bitte einen Erinnerungszeitpunkt wählen.';
            }
            if (!this.form.recurrence) {
                this.formErrors.recurrence = 'Bitte eine Wiederholung wählen.';
            }

            if (Object.keys(this.formErrors).length > 0) {
                // Trigger shake: set true → wait one frame → set false so it can re-fire
                this.shakeTrigger = true;
                this.$nextTick(() => { this.shakeTrigger = false; });
                return false;
            }
            return true;
        },

        /** Handle form submit – branches to create or update based on editingId */
        async submitForm() {
            if (!this.validateForm()) return;

            this.saving = true;
            try {
                if (this.editingId) {
                    await this.updateAppointment();
                } else {
                    await this.createAppointment();
                }
            } finally {
                this.saving = false;
            }
        },

        /** Reset the form to empty create-mode state */
        resetForm() {
            this.form       = { title: '', date: '', reminder_offset: '', recurrence: '' };
            this.formErrors = {};
            this.editingId  = null;
        },

        /** Populate the form with an existing appointment's data for editing */
        startEdit(appt) {
            this.editingId = appt.id;
            this.form = {
                title:           appt.title,
                date:            appt.date,
                reminder_offset: appt.reminder_offset,
                recurrence:      appt.recurrence,
            };
            this.formErrors = {};
            this.formOpen = true;
            // Scroll the form into view after it becomes visible
            this.$nextTick(() => {
                this.$el.querySelector('form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },

        /** Exit edit/create mode and hide the form panel */
        cancelEdit() {
            this.closeForm();
        },

        /** Open the form in create mode (called by the "+" button) */
        openNewForm() {
            this.editingId = null;
            this.form      = { title: '', date: '', reminder_offset: '', recurrence: '' };
            this.formErrors = {};
            this.formOpen  = true;
            this.$nextTick(() => {
                this.$el.querySelector('form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        /** Close and reset the form panel */
        closeForm() {
            this.formOpen = false;
            this.resetForm();
        },

        // ── CRUD operations ────────────────────────────────────────────

        /** POST a new appointment to the API and prepend it to the list */
        async createAppointment() {
            const res  = await fetch('/api/appointments', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(this.form),
            });
            const data = await res.json();

            if (!res.ok) {
                // Show server-side validation errors in the form
                this.formErrors = data.errors ?? {};
                Alpine.store('toast').show('Bitte Eingaben prüfen.', 'error');
                return;
            }

            this.appointments.push(data);
            this.resetForm();
            this.formOpen = false;
            Alpine.store('toast').show('Termin gespeichert!');
        },

        /** PUT updated appointment data to the API and refresh the list row */
        async updateAppointment() {
            const res  = await fetch(`/api/appointments/${this.editingId}`, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(this.form),
            });
            const data = await res.json();

            if (!res.ok) {
                this.formErrors = data.errors ?? {};
                Alpine.store('toast').show('Bitte Eingaben prüfen.', 'error');
                return;
            }

            // Replace the stale row in the array with the updated data
            const idx = this.appointments.findIndex(a => a.id === this.editingId);
            if (idx !== -1) this.appointments[idx] = data;

            this.resetForm();
            this.formOpen = false;
            Alpine.store('toast').show('Termin aktualisiert!');
        },

        /**
         * Delete an appointment with a confirmation dialog.
         * Adds a `_leaving` flag to trigger the CSS fade-out before removal.
         */
        async deleteAppt(appt) {
            if (!confirm(`„${appt.title}" wirklich löschen?`)) return;

            // Trigger fade-out animation
            appt._leaving = true;

            // Wait for the CSS animation to finish (250 ms) before removing the row
            await new Promise(r => setTimeout(r, 280));

            const res = await fetch(`/api/appointments/${appt.id}`, { method: 'DELETE' });
            if (res.status === 204 || res.ok) {
                this.appointments = this.appointments.filter(a => a.id !== appt.id);
                Alpine.store('toast').show('Termin gelöscht.');
            } else {
                appt._leaving = false;
                Alpine.store('toast').show('Löschen fehlgeschlagen.', 'error');
            }
        },

        // ── Formatting helpers ─────────────────────────────────────────

        /**
         * Format an ISO date string (YYYY-MM-DD) to the DD.MM. display format.
         * @param {string} isoDate
         * @returns {string}
         */
        formatDate(isoDate) {
            if (!isoDate) return '–';
            const [, month, day] = isoDate.split('-');
            return `${day}.${month}.`;
        },

        /** Return the human-readable label for a reminder_offset enum value */
        offsetLabel(val) {
            return this._offsetMap[val] ?? val;
        },

        /** Return the human-readable label for a recurrence enum value */
        recurrenceLabel(val) {
            return this._recurrenceMap[val] ?? val;
        },
    };
}
</script>
