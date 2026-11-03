<?php
$cfg = config();
$lat = (float) $cfg['map_lat'];
$lon = (float) $cfg['map_lon'];
$bbox = sprintf('%.5f,%.5f,%.5f,%.5f', $lon - 0.006, $lat - 0.003, $lon + 0.006, $lat + 0.003);
$mapEmbed = 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik&marker=' . $lat . ',' . $lon;
$mapLink  = 'https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lon . '#map=17/' . $lat . '/' . $lon;
$route    = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($cfg['street'] . ', ' . $cfg['zip'] . ' ' . $cfg['city']);

$page = [
    'slug'        => 'kontakt',
    'nav'         => 'kontakt',
    'title'       => 'Kontakt & Anfahrt',
    'description' => 'So finden Sie das Café am Schloss: Am Wall 10, 91583 Schillingsfürst. Telefon, Öffnungszeiten, Anfahrt und Parken.',
];
require APP . '/layout/header.php';
?>
<section class="page-hero">
  <div class="container d-flex flex-column align-items-center text-center gap-3">
    <p class="script page-hero__script mb-0">So finden Sie uns</p>
    <h1 class="page-hero__title mb-0">Kontakt &amp; Anfahrt</h1>
    <?= divider('divider--wide') ?>
  </div>
</section>

<section class="container section">
  <div class="row g-5 align-items-stretch">
    <div class="col-lg-5 d-flex flex-column gap-4">
      <div class="contact-block">
        <h2><i class="fa-light fa-location-dot" aria-hidden="true"></i>Adresse</h2>
        <p><?= e($cfg['site_name']) ?><br><?= e($cfg['street']) ?>, <?= e($cfg['zip'] . ' ' . $cfg['city']) ?></p>
      </div>
      <div class="contact-block">
        <h2><i class="fa-light fa-phone" aria-hidden="true"></i>Kontakt</h2>
        <p>Telefon: <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a><br>E-Mail: <a href="mailto:<?= e($cfg['email']) ?>"><?= e($cfg['email']) ?></a></p>
        <p class="note">Reservierungen nehmen wir gerne telefonisch entgegen.</p>
      </div>
      <div class="contact-block">
        <h2><i class="fa-light fa-clock" aria-hidden="true"></i>Öffnungszeiten</h2>
        <dl class="hours">
<?php foreach ($cfg['hours'] as [$day, $time]): ?>
          <dt><?= e($day) ?></dt><dd><?= e($time) ?></dd>
<?php endforeach; ?>
        </dl>
        <p class="note"><?= e($cfg['hours_note']) ?></p>
      </div>
      <div class="contact-block">
        <h2><i class="fa-light fa-square-parking" aria-hidden="true"></i>Anfahrt &amp; Parken</h2>
        <p>[Kurze Wegbeschreibung, z. B. ab Ortsmitte oder Autobahnausfahrt.]<br>[Hinweis zu Parkplätzen und Barrierefreiheit.]</p>
      </div>
      <div><a class="btn btn-cafe" href="<?= e($route) ?>" target="_blank" rel="noopener"><i class="fa-light fa-route" aria-hidden="true"></i>Route planen</a></div>
    </div>
    <div class="col-lg-7">
      <div class="map-consent" data-map-src="<?= e($mapEmbed) ?>">
        <div class="map-consent__inner">
          <i class="fa-light fa-map-location-dot map-consent__icon" aria-hidden="true"></i>
          <p class="mb-0">Mit dem Laden der Karte werden Daten an OpenStreetMap übertragen.<br>Mehr dazu in unserer <a href="<?= e(url('datenschutz')) ?>">Datenschutzerklärung</a>.</p>
          <button type="button" class="btn btn-cafe js-map-load"><i class="fa-light fa-map" aria-hidden="true"></i>Karte anzeigen</button>
          <a class="small" href="<?= e($mapLink) ?>" target="_blank" rel="noopener">Karte auf openstreetmap.org öffnen</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container pb-5 text-center">
  <img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img" width="480" height="152" loading="lazy">
</section>
<?php require APP . '/layout/footer.php'; ?>
