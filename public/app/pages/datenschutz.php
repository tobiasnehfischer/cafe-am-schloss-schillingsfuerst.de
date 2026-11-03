<?php
$page = [
    'slug'        => 'datenschutz',
    'nav'         => '',
    'title'       => 'Datenschutz',
    'description' => 'Datenschutzerklärung des Café am Schloss, Schillingsfürst.',
];
require APP . '/layout/header.php';
?>
<section class="container section">
  <div class="legal">
    <h1 class="page-hero__title">Datenschutz</h1>
    <?= divider('divider--legal') ?>
<?php readfile(APP . '/content/datenschutz.html'); ?>
  </div>
</section>
<?php require APP . '/layout/footer.php'; ?>
