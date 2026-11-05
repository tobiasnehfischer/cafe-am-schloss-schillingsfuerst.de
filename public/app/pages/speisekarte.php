<?php
$cfg   = config();
$card  = menu_card();
$menus = menus_available(content_load());

$anchor = static fn(string $title): string => 'karte-' . trim((string) preg_replace('~[^a-z0-9]+~', '-',
    strtr(mb_strtolower($title), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', '&' => 'und'])), '-');
$tags = [
    'vegetarisch' => 'fa-leaf',
    'vegan'       => 'fa-seedling',
];

$page = [
    'slug'        => 'speisekarte',
    'nav'         => 'speisekarte',
    'title'       => 'Speisekarte',
    'description' => 'Speisekarte, Sonntagskarte und Biergartenkarte des Café am Schloss in Schillingsfürst – '
        . ($card['kategorien'] ? 'zum Nachlesen und als PDF zum Herunterladen.' : 'zum Ansehen und Herunterladen.'),
];
require APP . '/layout/header.php';
?>
<section class="page-hero">
  <div class="container d-flex flex-column align-items-center text-center gap-3">
    <p class="script page-hero__script mb-0">Guten Appetit!</p>
    <h1 class="page-hero__title mb-0">Speisekarten</h1>
    <?= divider('divider--wide') ?>
    <p class="lead-text mb-0"><?= $menus ? 'Unsere aktuellen Karten finden Sie hier zum Ansehen und Herunterladen' : 'Fränkische Klassiker, Brotzeiten, Kaffee und hausgemachter Kuchen' ?><?php if ($menus && $card['kategorien']): ?> – die Speisekarte auch <a href="#speisekarte">direkt zum Nachlesen</a><?php endif; ?>.</p>
  </div>
</section>

<?php if ($menus): ?>
<section class="container section">
  <div class="row g-4 justify-content-center">
<?php foreach ($menus as $menu): ?>
    <div class="col-md-6 col-xl-4">
      <article class="menu-card h-100">
        <div class="icon-circle icon-circle--lg"><i class="fa-light <?= e($menu['icon']) ?>" aria-hidden="true"></i></div>
        <h2><?= e($menu['label']) ?></h2>
        <p class="menu-card__text"><?= e($menu['text']) ?></p>
        <p class="menu-card__meta">PDF · <?= e(format_size($menu['size'])) ?><?php if ($menu['updated'] !== ''): ?> · aktualisiert am <?= e(format_date($menu['updated'])) ?><?php endif; ?></p>
        <div class="menu-card__actions mt-auto pt-2">
          <a class="btn btn-cafe" href="<?= e($menu['url']) ?>" target="_blank" rel="noopener"><i class="fa-light fa-eye" aria-hidden="true"></i>Ansehen</a>
          <a class="btn btn-cafe-outline" href="<?= e($menu['url']) ?>" download><i class="fa-light fa-download" aria-hidden="true"></i>Herunterladen</a>
        </div>
      </article>
    </div>
<?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($card['kategorien']): ?>
<section class="band" id="speisekarte">
  <div class="container">
    <header class="section-head">
      <p class="script section-head__script">Aus unserer Küche</p>
      <h2 class="section-head__title">Unsere Speisekarte</h2>
    </header>
<?php if ($card['beispiel']): ?>
    <p class="sample-note">[Beispieldaten – die aktuelle Speisekarte folgt]</p>
<?php endif; ?>
    <nav class="menu-nav" aria-label="Kategorien der Speisekarte">
<?php foreach ($card['kategorien'] as $cat): ?>
      <a href="#<?= e($anchor($cat['titel'])) ?>"><?= e($cat['titel']) ?></a>
<?php endforeach; ?>
    </nav>

    <div class="row g-4">
<?php foreach ($card['kategorien'] as $cat): $id = $anchor($cat['titel']); ?>
      <div class="col-lg-6">
        <section class="menu-cat h-100" id="<?= e($id) ?>" aria-labelledby="<?= e($id) ?>-titel">
          <header class="menu-cat__head">
<?php if (!empty($cat['icon'])): ?>
            <div class="icon-circle icon-circle--sm"><i class="fa-light <?= e($cat['icon']) ?>" aria-hidden="true"></i></div>
<?php endif; ?>
            <div>
              <h3 id="<?= e($id) ?>-titel"><?= e($cat['titel']) ?></h3>
<?php if (!empty($cat['hinweis'])): ?>
              <p class="note"><?= e($cat['hinweis']) ?></p>
<?php endif; ?>
            </div>
          </header>
          <ul class="dishes">
<?php foreach ($cat['gerichte'] as $dish): ?>
            <li class="dish">
              <div class="dish__head">
                <span class="dish__name"><?= e($dish['name'] ?? '') ?><?php foreach ((array) ($dish['merkmale'] ?? []) as $tag): if (isset($tags[$tag])): ?> <span class="dish__tag"><i class="fa-light <?= $tags[$tag] ?>" aria-hidden="true"></i><?= e($tag) ?></span><?php endif; endforeach; ?></span>
                <span class="dish__dots" aria-hidden="true"></span>
                <span class="dish__price"><?= e(format_price($dish['preis'] ?? '')) ?></span>
              </div>
<?php if (!empty($dish['text'])): ?>
              <p class="dish__text"><?= e($dish['text']) ?></p>
<?php endif; ?>
            </li>
<?php endforeach; ?>
          </ul>
        </section>
      </div>
<?php endforeach; ?>
    </div>

    <div class="d-flex flex-column align-items-center text-center gap-2 mt-5">
<?php if ($card['fussnote'] !== ''): ?>
      <p class="note"><?= e($card['fussnote']) ?></p>
<?php endif; ?>
      <p class="note">Informationen zu Allergenen und Zusatzstoffen erhalten Sie bei unserem Service-Team.</p>
    </div>
  </div>
</section>
<?php elseif ($menus): ?>
<p class="text-center note mb-5 mx-auto" style="max-width:720px">Informationen zu Allergenen und Zusatzstoffen erhalten Sie bei unserem Service-Team.</p>
<?php else: ?>
<section class="container section">
  <div class="menu-card mx-auto empty-state">
    <div class="icon-circle icon-circle--lg"><i class="fa-light fa-book-open" aria-hidden="true"></i></div>
    <p class="mb-0">Unsere Karten werden gerade überarbeitet. Gerne beraten wir Sie telefonisch unter <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a> oder direkt bei uns im Café.</p>
  </div>
</section>
<?php endif; ?>

<section class="container <?= $card['kategorien'] ? 'py-5' : 'pb-5' ?> text-center">
  <span class="welcome-wrap"><img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img welcome-img--sm" width="420" height="133" loading="lazy"></span>
</section>
<?php require APP . '/layout/footer.php'; ?>
