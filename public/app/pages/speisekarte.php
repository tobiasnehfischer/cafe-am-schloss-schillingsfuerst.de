<?php
$cfg   = config();
$online = menu_online(); // Speisekarte als Text (Pflegeoberfläche)
$menus = menus_available(content_load());
// Teile der Speisekarte (Warme Küche, Vesper, …) gemeinsam als Raster, alle übrigen Karten (Sonntagskarte) groß darunter
$parts  = array_filter($menus, static fn(array $m): bool => $m['group'] === 'speisekarte');
$others = array_diff_key($menus, $parts);

$page = [
    'slug'        => 'speisekarte',
    'nav'         => 'speisekarte',
    'title'       => 'Speisekarte',
    'description' => 'Speisekarte (Warme Küche, Vesper, Nachtisch, Getränke) und Sonntagskarte des Café am Schloss in Schillingsfürst – '
        . ($online ? 'zum Nachlesen und als PDF zum Herunterladen.' : 'zum Ansehen und Herunterladen.'),
];
require APP . '/layout/header.php';
?>
<section class="page-hero">
  <div class="container d-flex flex-column align-items-center text-center gap-3">
    <p class="script page-hero__script mb-0">Guten Appetit!</p>
    <h1 class="page-hero__title mb-0">Speisekarten</h1>
    <?= divider('divider--wide') ?>
    <p class="lead-text mb-0"><?= $menus ? 'Unsere aktuellen Karten finden Sie hier zum Ansehen und Herunterladen' : ($online ? 'Unsere aktuelle Speisekarte zum Nachlesen' : 'Fränkische Klassiker, Brotzeiten, Kaffee und hausgemachter Kuchen') ?><?php if ($menus && $online): ?> – die Speisekarte auch <a href="#speisekarte">direkt zum Nachlesen</a><?php endif; ?>.</p>
  </div>
</section>

<?php if ($parts): ?>
<section class="container section" id="karten">
  <header class="section-head">
    <p class="script section-head__script">Aus unserer Küche</p>
    <h2 class="section-head__title">Speisekarte</h2>
  </header>
  <div class="row g-4 justify-content-center">
<?php foreach ($parts as $menu): ?>
    <div class="col-sm-6 col-xl-3">
      <article class="menu-card menu-card--compact h-100">
        <div class="icon-circle icon-circle--lg"><i class="fa-light <?= e($menu['icon']) ?>" aria-hidden="true"></i></div>
        <h3><?= e($menu['label']) ?></h3>
        <p class="menu-card__text"><?= e($menu['text']) ?></p>
        <p class="menu-card__meta">PDF · <?= e(format_size($menu['size'])) ?><?php if ($menu['updated'] !== ''): ?><br>aktualisiert am <?= e(format_date($menu['updated'])) ?><?php endif; ?></p>
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

<?php if ($others): ?>
<section class="band">
  <div class="container">
    <div class="row g-4 justify-content-center">
<?php foreach ($others as $menu): ?>
      <div class="col-md-8 col-lg-6">
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
  </div>
</section>
<?php endif; ?>

<?php if ($online): ?>
<section class="<?= $others ? 'section' : 'band' ?>" id="speisekarte">
  <div class="container">
    <header class="section-head">
      <p class="script section-head__script">Zum Nachlesen</p>
      <h2 class="section-head__title">Unsere Speisekarte</h2>
    </header>
<?php if (count(array_filter(array_column($online, 'title'))) > 1): ?>
    <nav class="menu-nav" aria-label="Teile der Speisekarte">
<?php foreach ($online as $part): if ($part['title'] === '') { continue; } ?>
      <a href="#<?= e($part['id']) ?>"><?= e($part['title']) ?></a>
<?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php foreach ($online as $part): ?>
    <section class="menu-part" id="<?= e($part['id']) ?>"<?= $part['title'] !== '' ? ' aria-labelledby="' . e($part['id']) . '-titel"' : '' ?>>
<?php if ($part['title'] !== ''): ?>
      <h3 class="menu-part__title" id="<?= e($part['id']) ?>-titel"><?= e($part['title']) ?></h3>
<?php endif; ?>
<?php foreach ($part['intro'] as $intro): ?>
      <p class="menu-part__intro"><?= e_lines($intro['lines']) ?></p>
<?php endforeach; ?>
<?php $cols = menu_columns($part['cats']); // zwei ausgeglichene Spalten, Reihenfolge bleibt erhalten ?>
      <div class="row g-4 justify-content-center">
<?php foreach ($cols as $colCats): ?>
        <div class="<?= count($cols) === 1 ? 'col-lg-8' : 'col-lg-6' ?> d-flex flex-column gap-4">
<?php foreach ($colCats as $cat): $open = false; ?>
            <section class="menu-cat">
<?php if ($cat['title'] !== ''): ?>
              <header class="menu-cat__head"><h4><?= e($cat['title']) ?></h4></header>
<?php endif; ?>
<?php foreach ($cat['entries'] as $entry): ?>
<?php if ($entry['type'] !== 'item' && $open): $open = false; ?>
              </ul>
<?php endif; ?>
<?php if ($entry['type'] === 'sub'): ?>
              <h5 class="menu-sub"><?= e($entry['title']) ?></h5>
<?php elseif ($entry['type'] === 'text'): ?>
              <p class="menu-cat__text"><?= e_lines($entry['lines']) ?></p>
<?php else: if (!$open): $open = true; ?>
              <ul class="dishes">
<?php endif; ?>
                <li class="dish<?= $entry['text'] ? '' : ' dish--short' ?>">
                  <div class="dish__head">
<?php if ($entry['prices']): ?>
                    <span class="dish__price"><?php foreach ($entry['prices'] as $price): ?><span><?php if ($price['label'] !== ''): ?><span class="dish__size"><?= e($price['label']) ?></span> <?php endif; ?><?= e($price['amount']) ?></span><?php endforeach; ?></span>
<?php endif; ?>
                    <div class="dish__main">
                      <div class="dish__line">
                        <span class="dish__name"><?= e($entry['name']) ?></span>
<?php if ($entry['prices']): ?>
                        <span class="dish__dots" aria-hidden="true"></span>
<?php endif; ?>
                      </div>
<?php if ($entry['text']): ?>
                      <p class="dish__text"><?= e_lines($entry['text']) ?></p>
<?php endif; ?>
                    </div>
                  </div>
                </li>
<?php endif; ?>
<?php endforeach; ?>
<?php if ($open): ?>
              </ul>
<?php endif; ?>
            </section>
<?php endforeach; ?>
        </div>
<?php endforeach; ?>
      </div>
    </section>
<?php endforeach; ?>

    <div class="d-flex flex-column align-items-center text-center gap-2 mt-5">
      <p class="note">Alle Preise inklusive Bedienung und MwSt.</p>
      <p class="note">Wenn Sie Fragen zu Allergenen oder Zusatzstoffen haben, sprechen Sie bitte unsere Mitarbeiter an.</p>
    </div>
  </div>
</section>
<?php elseif ($menus): ?>
<p class="text-center note mx-auto <?= $others ? 'pt-5 mb-4' : 'mb-5' ?>" style="max-width:720px">Informationen zu Allergenen und Zusatzstoffen erhalten Sie bei unserem Service-Team.</p>
<?php else: ?>
<section class="container section">
  <div class="menu-card mx-auto empty-state">
    <div class="icon-circle icon-circle--lg"><i class="fa-light fa-book-open" aria-hidden="true"></i></div>
    <p class="mb-0">Unsere Karten werden gerade überarbeitet. Gerne beraten wir Sie telefonisch unter <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a> oder direkt bei uns im Café.</p>
  </div>
</section>
<?php endif; ?>

<section class="container <?= $online ? 'py-5' : 'pb-5' ?> text-center">
  <span class="welcome-wrap"><img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img welcome-img--sm" width="420" height="133" loading="lazy"></span>
</section>
<?php require APP . '/layout/footer.php'; ?>
