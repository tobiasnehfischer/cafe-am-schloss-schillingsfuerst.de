<?php
$cfg   = config();
$menus = menus_available(content_load());

$page = [
    'slug'        => 'speisekarte',
    'nav'         => 'speisekarte',
    'title'       => 'Speisekarte',
    'description' => 'Speisekarte, Sonntagskarte und Biergartenkarte des Café am Schloss in Schillingsfürst zum Ansehen und Herunterladen.',
];
require APP . '/layout/header.php';
?>
<section class="page-hero">
  <div class="container d-flex flex-column align-items-center text-center gap-3">
    <p class="script page-hero__script mb-0">Guten Appetit!</p>
    <h1 class="page-hero__title mb-0">Speisekarten</h1>
    <?= divider('divider--wide') ?>
    <p class="lead-text mb-0">Unsere aktuellen Karten finden Sie hier zum Ansehen und Herunterladen.</p>
  </div>
</section>

<section class="container section">
<?php if ($menus): ?>
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
<?php else: ?>
  <div class="menu-card mx-auto empty-state">
    <div class="icon-circle icon-circle--lg"><i class="fa-light fa-book-open" aria-hidden="true"></i></div>
    <p class="mb-0">Unsere Karten werden gerade überarbeitet. Gerne beraten wir Sie telefonisch unter <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a> oder direkt bei uns im Café.</p>
  </div>
<?php endif; ?>
  <p class="text-center note mt-5 mb-0 mx-auto" style="max-width:720px">Informationen zu Allergenen und Zusatzstoffen erhalten Sie bei unserem Service-Team.</p>
</section>

<section class="container pb-5 text-center">
  <img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img welcome-img--sm" width="420" height="133" loading="lazy">
</section>
<?php require APP . '/layout/footer.php'; ?>
