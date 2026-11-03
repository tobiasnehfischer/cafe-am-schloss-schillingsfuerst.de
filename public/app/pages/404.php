<?php
$page = [
    'slug'    => '',
    'nav'     => '',
    'title'   => 'Seite nicht gefunden',
    'noindex' => true,
];
require APP . '/layout/header.php';
?>
<section class="page-hero">
  <div class="container d-flex flex-column align-items-center text-center gap-3 py-4">
    <p class="script page-hero__script mb-0">Hoppla!</p>
    <h1 class="page-hero__title mb-0">Seite nicht gefunden</h1>
    <?= divider('divider--wide') ?>
    <p class="lead-text mb-2">Die gewünschte Seite gibt es leider nicht (mehr).</p>
    <div class="d-flex gap-3 flex-wrap justify-content-center">
      <a class="btn btn-cafe" href="<?= e(url()) ?>">Zur Startseite</a>
      <a class="btn btn-cafe-outline" href="<?= e(url('speisekarte')) ?>">Speisekarten</a>
    </div>
  </div>
</section>
<?php require APP . '/layout/footer.php'; ?>
