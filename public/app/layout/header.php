<?php
/**
 * Seitenkopf. Erwartet $page = ['title', 'description', 'nav', 'notice' (optional)].
 * @var array $page
 */
$cfg   = config();
$slug  = $page['slug'] ?? '';
$title = ($page['title'] ?? '') !== '' ? $page['title'] . ' – ' . $cfg['site_name'] . ' Schillingsfürst' : $cfg['site_name'] . ' Schillingsfürst – Fränkische Küche';
$navItems = [
    'start'           => ['', 'Startseite'],
    'speisekarte'     => ['speisekarte', 'Speisekarte'],
    'feierlichkeiten' => ['feierlichkeiten', 'Feierlichkeiten'],
    'kontakt'         => ['kontakt', 'Kontakt &amp; Anfahrt'],
];
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if (!empty($page['description'])): ?>
<meta name="description" content="<?= e($page['description']) ?>">
<?php endif; ?>
<?php if (!empty($cfg['base_url']) && http_response_code() === 200): ?>
<link rel="canonical" href="<?= e($cfg['base_url'] . '/' . $slug) ?>">
<?php endif; ?>
<?php if (!empty($page['noindex'])): ?>
<meta name="robots" content="noindex">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:locale" content="de_DE">
<meta name="theme-color" content="#24361E">
<link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/lilie.svg')) ?>">
<link rel="preload" href="<?= e(url('assets/fonts/noto-serif/noto-serif-v33-latin-regular.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('assets/fonts/abstract-spectrum.ttf')) ?>" as="font" type="font/ttf" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/fontawesome/css/fontawesome.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/fontawesome/css/light.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
</head>
<body class="page-<?= e($page['nav'] ?? 'none') ?>">
<a class="visually-hidden-focusable skip-link" href="#inhalt">Zum Inhalt springen</a>

<?php if (!empty($page['notice'])): ?>
<aside class="notice-bar" aria-label="Aktueller Hinweis">
  <div class="container d-flex align-items-center justify-content-center gap-3 text-center">
    <i class="fa-light fa-circle-info notice-bar__icon" aria-hidden="true"></i>
    <p class="mb-0"><?= nl2br(e($page['notice'])) ?></p>
  </div>
</aside>
<?php endif; ?>

<header class="site-header sticky-top">
  <nav class="navbar navbar-expand-lg" aria-label="Hauptnavigation">
    <div class="container">
      <a class="navbar-brand" href="<?= e(url()) ?>" aria-label="<?= e($cfg['site_name']) ?> – zur Startseite">
        <img src="<?= e(asset('assets/img/logo-text-only.svg')) ?>" alt="<?= e($cfg['site_name']) ?>" width="112" height="64">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hauptnavigation" aria-controls="hauptnavigation" aria-expanded="false" aria-label="Menü öffnen">
        <i class="fa-light fa-bars" aria-hidden="true"></i>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="hauptnavigation">
        <ul class="navbar-nav">
<?php foreach ($navItems as $key => [$href, $label]): $active = ($page['nav'] ?? '') === $key; ?>
          <li class="nav-item"><a class="nav-link<?= $active ? ' active' : '' ?>" href="<?= e(url($href)) ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= $label ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
    </div>
  </nav>
</header>

<main id="inhalt">
