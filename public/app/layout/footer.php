<?php
/** Seitenfuß */
$cfg = config();
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4 g-lg-5 py-5">
      <div class="col-12 col-md-6 col-lg-4">
        <img src="<?= e(asset('assets/img/logo-text-only.svg')) ?>" alt="<?= e($cfg['site_name']) ?>" width="150" height="86" class="mb-3">
        <p class="mb-1 ls-wide">Fränkische Küche</p>
        <p class="mb-0 small ls-wider text-muted-green">Regional • Saisonal • Gutbürgerlich</p>
      </div>
      <div class="col-12 col-sm-6 col-lg">
        <h2 class="footer-title">Kontakt</h2>
        <p class="mb-0"><?= e($cfg['street']) ?><br><?= e($cfg['zip'] . ' ' . $cfg['city']) ?><br>
          <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a></p>
      </div>
      <div class="col-12 col-sm-6 col-lg">
        <h2 class="footer-title">Öffnungszeiten</h2>
        <p class="mb-0"><?= implode('<br>', array_map('e', $cfg['hours_short'])) ?></p>
      </div>
      <nav class="col-12 col-sm-6 col-lg" aria-label="Seiten">
        <h2 class="footer-title">Seiten</h2>
        <ul class="list-unstyled mb-0">
          <li><a href="<?= e(url()) ?>">Startseite</a></li>
          <li><a href="<?= e(url('speisekarte')) ?>">Speisekarte</a></li>
          <li><a href="<?= e(url('feierlichkeiten')) ?>">Feierlichkeiten</a></li>
          <li><a href="<?= e(url('kontakt')) ?>">Kontakt &amp; Anfahrt</a></li>
        </ul>
      </nav>
    </div>
  </div>
  <div class="site-footer__bottom">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3 py-3 small">
      <span>© <?= date('Y') ?> <?= e($cfg['site_name']) ?>, <?= e($cfg['city']) ?></span>
      <span class="d-flex gap-4">
        <a href="<?= e(url('impressum')) ?>">Impressum</a>
        <a href="<?= e(url('datenschutz')) ?>">Datenschutz</a>
      </span>
    </div>
  </div>
</footer>

<script src="<?= e(asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
</body>
</html>
