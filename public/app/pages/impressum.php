<?php
$page = [
    'slug'        => 'impressum',
    'nav'         => '',
    'title'       => 'Impressum',
    'description' => 'Impressum des Café am Schloss, Schillingsfürst.',
];
require APP . '/layout/header.php';
?>
<section class="container section">
  <div class="legal">
    <h1 class="page-hero__title">Impressum</h1>
    <?= divider('divider--legal') ?>
<?php readfile(APP . '/content/impressum.html'); ?>
  </div>
</section>
<?php require APP . '/layout/footer.php'; ?>
