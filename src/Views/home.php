<!--
    Home page partial – included by layout.php
    Shows a hero section and informational content about the service.
    Elements carry [data-reveal] so the scroll-reveal script can animate them in.
-->

<!-- Hero section -->
<section class="flex flex-col md:flex-row items-start gap-10 mb-14" data-reveal>
    <!-- Text column -->
    <div class="flex-1">
        <h1 class="text-3xl font-bold text-slate-900 mb-4">Ihr persönlicher Erinnerungskalender</h1>
        <p class="text-slate-600 leading-relaxed mb-4">
            Vergessen Sie nie wieder einen wichtigen Termin. Mit <strong>Reminder Calendar</strong>
            tragen Sie Geburtstage, Jubiläen und persönliche Ereignisse ein – und erhalten rechtzeitig
            eine Erinnerungsmail, bevor der Tag kommt.
        </p>
        <p class="text-slate-600 leading-relaxed mb-6">
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
            invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam
            et justo duo dolores et ea rebum.
        </p>
        <?php if (!$currentUser): ?>
        <a href="/register"
           class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 text-white font-semibold
                  rounded-lg hover:bg-teal-500 transition-colors">
            Jetzt kostenlos starten
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
        <?php else: ?>
        <a href="/calendar"
           class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 text-white font-semibold
                  rounded-lg hover:bg-teal-500 transition-colors">
            Zum Kalender
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </a>
        <?php endif ?>
    </div>

    <!-- Placeholder image -->
    <div class="w-full md:w-64 lg:w-80 shrink-0">
        <img src="/assets/images/hero.jpg"
             alt="Person am Telefon – symbolisiert Kommunikation und Erinnerungen"
             class="w-full rounded-xl shadow-md object-cover aspect-[4/3] bg-slate-200"
             onerror="this.style.display='none'">
        <!-- Fallback placeholder if the image file hasn't been added yet -->
        <div class="w-full rounded-xl shadow-inner bg-gradient-to-br from-teal-50 to-slate-200
                    aspect-[4/3] flex items-center justify-center text-slate-400 text-sm hidden"
             id="img-fallback">
            Bild folgt
        </div>
    </div>
</section>

<!-- Feature cards -->
<section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-14">
    <?php
    $features = [
        ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
         'title' => 'Termine eintragen',
         'text'  => 'Datum, Bezeichnung und Erinnerungszeitpunkt in Sekunden erfasst.'],
        ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
         'title' => 'E-Mail-Erinnerung',
         'text'  => 'Erhalten Sie eine Benachrichtigung 1 Tag bis 2 Wochen im Voraus.'],
        ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
         'title' => 'Wiederholend',
         'text'  => 'Jährliche Geburtstage oder wöchentliche Meetings – ganz flexibel.'],
    ];
    foreach ($features as $i => $f): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 border-t-2 border-t-teal-500 p-6 transition-all duration-200 hover:shadow-md hover:-translate-y-1 cursor-default" data-reveal>
        <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $f['icon'] ?>"/>
            </svg>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1"><?= $f['title'] ?></h3>
        <p class="text-sm text-slate-500"><?= $f['text'] ?></p>
    </div>
    <?php endforeach ?>
</section>

<!-- Additional lorem ipsum body text -->
<section class="prose prose-slate max-w-none" data-reveal>
    <p>
        Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
        invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
    </p>
    <p>
        At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,
        no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
        consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
        dolore magna aliquyam erat, sed diam voluptua.
    </p>
    <p>
        Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat,
        vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio
        dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait
        nulla facilisi.
    </p>
</section>

<!-- Scroll-reveal script – runs once after all content has loaded -->
<script>
/**
 * Scroll-reveal using IntersectionObserver.
 * Any element with [data-reveal] starts invisible (see app.css)
 * and gains the 'revealed' class as it enters the viewport.
 */
document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    // Stop observing once revealed – no need to toggle back
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 } // trigger when 15 % of the element is visible
    );

    document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
});
</script>
