<?php
$cfg     = config();
$content = content_load();
$menus   = menus_available($content);

// Eröffnungs-Hero bis einschließlich config 'opening_hero_until'. Lokal (php -S) testbar mit ?datum=YYYY-MM-DD.
$today = PHP_SAPI === 'cli-server' && preg_match('~^\d{4}-\d{2}-\d{2}$~', (string) ($_GET['datum'] ?? ''))
    ? (string) $_GET['datum']
    : date('Y-m-d');
$openingHero = $today <= (string) ($cfg['opening_hero_until'] ?? '');

$page = [
    'slug'        => '',
    'nav'         => 'start',
    'title'       => '',
    'description' => ($openingHero ? 'Eröffnung am Samstag, 21.11.2026 ab 14 Uhr mit Kaffee und Kuchen, ab 22.11. Mittagstisch. ' : '')
        . 'Café am Schloss in Schillingsfürst: fränkische Küche – regional, saisonal, gutbürgerlich. Mittagessen, Kaffee und hausgemachter Kuchen, Sonntagsbraten und Feierlichkeiten.',
];
require APP . '/layout/header.php';
?>
<?php if ($openingHero): ?>
<section class="hero hero--event">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 text-center">
        <picture>
          <source srcset="<?= e(asset('assets/img/logo.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="Logo Café am Schloss mit Zeichnung des Schlosses" width="690" height="620" class="hero__logo img-fluid" fetchpriority="high">
        </picture>
      </div>
      <div class="col-lg-6 d-flex flex-column gap-4 align-items-center text-center">
        <h1 class="mb-0 d-flex flex-column align-items-center gap-3">
          <span class="script event__title">Eröffnung</span>
          <span class="event__date">am Samstag, 21.11.2026</span>
        </h1>
        <p class="event__time mb-0">ab 14 Uhr</p>
        <p class="script event__sub mb-0">
          <i class="fa-light fa-mug-hot" aria-hidden="true"></i>
          <span>Kaffee und Kuchen</span>
          <i class="fa-light fa-cake-slice" aria-hidden="true"></i>
        </p>
        <?= divider('divider--hero') ?>
        <p class="lead-text event__lead mb-0">Wir stellen uns vor und heißen Sie herzlich willkommen!</p>
        <div class="event__next">
          <div class="icon-circle"><i class="fa-light fa-utensils" aria-hidden="true"></i></div>
          <div class="text-start">
            <p class="event__next-label mb-0">Ab Sonntag, 22.11.</p>
            <p class="event__next-title mb-0">Mittagstisch</p>
          </div>
        </div>
        <div class="d-flex gap-3 flex-wrap justify-content-center">
          <a class="btn btn-cafe" href="<?= e(url('speisekarte')) ?>">Unsere Speisekarten</a>
          <a class="btn btn-cafe-outline" href="<?= e(url('kontakt')) ?>">Anfahrt &amp; Kontakt</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php else: ?>
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 text-center">
        <picture>
          <source srcset="<?= e(asset('assets/img/logo.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="Logo Café am Schloss mit Zeichnung des Schlosses" width="690" height="620" class="hero__logo img-fluid" fetchpriority="high">
        </picture>
      </div>
      <div class="col-lg-6 d-flex flex-column gap-4 align-items-center text-center">
        <p class="script hero__script mb-0">Herzlich willkommen!</p>
        <?= divider('divider--hero') ?>
        <h1 class="hero__title mb-0">Fränkische Küche</h1>
        <p class="tagline mb-0">Regional <span>•</span> Saisonal <span>•</span> Gutbürgerlich</p>
        <p class="lead-text mb-0">Schön, dass Sie da sind! Im Café am Schloss in Schillingsfürst erwartet Sie ehrliche fränkische Küche mit Zutaten aus der Region – vom gemütlichen Mittagessen über Kaffee und hausgemachten Kuchen bis zum Sonntagsbraten im Kreis der Familie.</p>
        <div class="d-flex gap-3 flex-wrap justify-content-center">
          <a class="btn btn-cafe" href="<?= e(url('speisekarte')) ?>">Unsere Speisekarten</a>
          <a class="btn btn-cafe-outline" href="<?= e(url('kontakt')) ?>">Anfahrt &amp; Kontakt</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section container">
  <header class="section-head">
    <p class="script section-head__script">Gut zu wissen</p>
    <h2 class="section-head__title">Ihr Besuch bei uns</h2>
  </header>
  <div class="row g-4">
    <div class="col-md-6 col-lg-4">
      <article class="info-card h-100">
        <div class="icon-circle"><i class="fa-light fa-location-dot" aria-hidden="true"></i></div>
        <h3>Anschrift &amp; Kontakt</h3>
        <p><?= e($cfg['site_name']) ?><br><?= e($cfg['street']) ?><br><?= e($cfg['zip'] . ' ' . $cfg['city']) ?></p>
        <p>Telefon: <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a></p>
        <a class="info-card__link mt-auto" href="<?= e(url('kontakt')) ?>">Anfahrt ansehen <i class="fa-light fa-arrow-right" aria-hidden="true"></i></a>
      </article>
    </div>
    <div class="col-md-6 col-lg-4">
      <article class="info-card h-100">
        <div class="icon-circle"><i class="fa-light fa-clock" aria-hidden="true"></i></div>
        <h3>Öffnungszeiten</h3>
        <dl class="hours">
<?php foreach ($cfg['hours'] as [$day, $time]): ?>
          <dt><?= e($day) ?></dt><dd><?= e_lines($time) ?></dd>
<?php endforeach; ?>
        </dl>
        <p class="note"><?= e_lines($cfg['hours_note']) ?></p>
      </article>
    </div>
    <div class="col-md-12 col-lg-4">
      <article class="info-card h-100">
        <div class="icon-circle"><i class="fa-light fa-book-open" aria-hidden="true"></i></div>
        <h3>Speisekarten</h3>
        <p>Fränkische Klassiker, saisonale Gerichte, Kaffee und hausgemachter Kuchen – und sonntags unsere eigene Sonntagskarte.</p>
<?php if (menu_card()['kategorien']): ?>
        <div class="mt-auto d-grid gap-2">
          <a class="btn btn-cafe" href="<?= e(url('speisekarte')) ?>"><i class="fa-light fa-utensils" aria-hidden="true"></i>Zur Speisekarte</a>
<?php foreach (array_diff_key($menus, ['speisekarte' => true]) as $menu): ?>
          <a class="btn btn-cafe-outline" href="<?= e($menu['url']) ?>" target="_blank" rel="noopener"><i class="fa-light fa-file-pdf" aria-hidden="true"></i><?= e($menu['label']) ?> (PDF)</a>
<?php endforeach; ?>
        </div>
<?php elseif ($menus): ?>
        <div class="mt-auto d-grid gap-2">
<?php $first = true; foreach ($menus as $menu): ?>
          <a class="btn <?= $first ? 'btn-cafe' : 'btn-cafe-outline' ?>" href="<?= e($menu['url']) ?>" target="_blank" rel="noopener"><i class="fa-light fa-file-pdf" aria-hidden="true"></i><?= e($menu['label']) ?> (PDF)</a>
<?php $first = false; endforeach; ?>
        </div>
<?php else: ?>
        <p class="note mt-auto">Unsere aktuellen Karten erhalten Sie direkt bei uns im Café.</p>
<?php endif; ?>
      </article>
    </div>
  </div>
</section>

<section class="band">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 d-flex flex-column gap-3 align-items-center text-center">
        <p class="script band__script mb-0">Feiern am Schloss</p>
        <p class="lead-text mb-0">Konfirmation, Kommunion, Taufe, Hochzeit, runder Geburtstag oder Trauerkaffee: Wir richten Ihre Feier aus – mit fränkischem Menü oder Buffet, ganz nach Ihren Wünschen.</p>
        <a class="btn btn-cafe" href="<?= e(url('feierlichkeiten')) ?>">Mehr zu Feierlichkeiten</a>
      </div>
    </div>
  </div>
</section>

<section class="container section text-center">
  <span class="welcome-wrap"><img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img" width="480" height="152" loading="lazy"></span>
</section>
<?php require APP . '/layout/footer.php'; ?>
