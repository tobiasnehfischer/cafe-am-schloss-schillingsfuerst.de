<?php
$cfg = config();

$occasions = ['Konfirmation / Kommunion', 'Hochzeit', 'Taufe', 'Geburtstag / Jubiläum', 'Trauerkaffee', 'Firmen- / Vereinsfeier', 'Sonstiges'];
$form   = ['name' => '', 'tel' => '', 'email' => '', 'anlass' => '', 'datum' => '', 'personen' => '', 'nachricht' => '', 'datenschutz' => false];
$errors = [];
$sent   = isset($_GET['gesendet']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach (['name', 'tel', 'email', 'anlass', 'datum', 'personen', 'nachricht'] as $f) {
        $form[$f] = trim((string) ($_POST[$f] ?? ''));
    }
    $form['datenschutz'] = !empty($_POST['datenschutz']);

    $isBot = trim((string) ($_POST['website'] ?? '')) !== '' || !form_token_valid((string) ($_POST['token'] ?? ''));

    if ($form['name'] === '' || mb_strlen($form['name']) > 120) {
        $errors['name'] = 'Bitte geben Sie Ihren Namen an.';
    }
    if ($form['tel'] === '' && $form['email'] === '') {
        $errors['tel'] = 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.';
    }
    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Die E-Mail-Adresse ist ungültig.';
    }
    if ($form['tel'] !== '' && !preg_match('/^[0-9 +()\/\-]{5,30}$/', $form['tel'])) {
        $errors['tel'] = 'Die Telefonnummer ist ungültig.';
    }
    if ($form['anlass'] !== '' && !in_array($form['anlass'], $occasions, true)) {
        $form['anlass'] = '';
    }
    if ($form['datum'] !== '' && !DateTimeImmutable::createFromFormat('!Y-m-d', $form['datum'])) {
        $errors['datum'] = 'Bitte ein gültiges Datum wählen.';
    }
    if ($form['personen'] !== '' && (!ctype_digit($form['personen']) || (int) $form['personen'] < 1 || (int) $form['personen'] > 999)) {
        $errors['personen'] = 'Bitte eine Personenzahl angeben.';
    }
    if (mb_strlen($form['nachricht']) > 5000) {
        $errors['nachricht'] = 'Die Nachricht ist zu lang.';
    }
    if (!$form['datenschutz']) {
        $errors['datenschutz'] = 'Bitte bestätigen Sie die Datenschutzerklärung.';
    }

    if ($isBot && !$errors) {
        // Spam: so tun, als wäre alles gut
        redirect(url('feierlichkeiten') . '?gesendet=1#anfrage');
    }
    if ($isBot) {
        $errors['form'] = 'Das Formular ist abgelaufen. Bitte senden Sie es erneut.';
    }

    if (!$errors) {
        $datum = $form['datum'] !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $form['datum'])->format('d.m.Y') : '–';
        $body  = "Neue Anfrage über cafe-am-schloss-schillingsfuerst.de (Feierlichkeiten)\n\n"
            . 'Name:        ' . $form['name'] . "\n"
            . 'Telefon:     ' . ($form['tel'] ?: '–') . "\n"
            . 'E-Mail:      ' . ($form['email'] ?: '–') . "\n"
            . 'Anlass:      ' . ($form['anlass'] ?: '–') . "\n"
            . 'Wunschdatum: ' . $datum . "\n"
            . 'Personen:    ' . ($form['personen'] ?: '–') . "\n\n"
            . "Nachricht:\n" . ($form['nachricht'] ?: '–') . "\n\n"
            . 'Gesendet am ' . date('d.m.Y \u\m H:i') . " Uhr\n";
        $subject = 'Anfrage Feier: ' . ($form['anlass'] ?: 'ohne Anlass') . ' – ' . $form['name'];

        require_once APP . '/mailer.php';
        $ok = send_site_mail($subject, $body, $form['email'], $form['name']);

        if ($ok) {
            redirect(url('feierlichkeiten') . '?gesendet=1#anfrage');
        }
        $errors['form'] = 'Ihre Anfrage konnte leider nicht gesendet werden. Bitte rufen Sie uns an: ' . $cfg['phone'];
    }
}

$page = [
    'slug'        => 'feierlichkeiten',
    'nav'         => 'feierlichkeiten',
    'title'       => 'Feierlichkeiten',
    'description' => 'Konfirmation, Hochzeit, Taufe, Geburtstag oder Trauerkaffee: Feiern im Café am Schloss in Schillingsfürst – mit fränkischem Menü oder Buffet.',
];
require APP . '/layout/header.php';

$err = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>' : '';
};
$inv = static fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<section class="hero hero--sub">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 d-flex flex-column gap-4 align-items-start">
        <p class="script hero__script mb-0">Feiern am Schloss</p>
        <?= divider('divider--hero') ?>
        <h1 class="hero__title mb-0">Feierlichkeiten</h1>
        <p class="lead-text mb-0">Ob Konfirmation, Hochzeit oder runder Geburtstag – bei uns feiern Sie in gemütlicher Atmosphäre am Schloss. Wir kümmern uns um Essen, Getränke und eine schön gedeckte Tafel, damit Sie den Tag mit Ihren Gästen genießen können.</p>
        <div class="d-flex gap-3 flex-wrap">
          <a class="btn btn-cafe" href="#anfrage">Feier anfragen</a>
          <a class="btn btn-cafe-outline" href="tel:<?= e($cfg['phone_link']) ?>"><i class="fa-light fa-phone" aria-hidden="true"></i><?= e($cfg['phone']) ?></a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="photo-placeholder">[Foto: Festsaal / gedeckte Tafel]</div>
      </div>
    </div>
  </div>
</section>

<section class="container section">
  <header class="section-head">
    <p class="script section-head__script">Für jeden Anlass</p>
    <h2 class="section-head__title">Wir richten aus</h2>
  </header>
  <div class="row g-4">
<?php
$items = [
    ['fa-church', 'Konfirmation &amp; Kommunion', 'Der große Tag im Kreis der Familie – mit festlichem Mittagessen und Kaffee und Kuchen am Nachmittag.'],
    ['fa-rings-wedding', 'Hochzeit', 'Ob kleine Feier nach dem Standesamt oder Hochzeitsessen – wir planen den Ablauf gemeinsam mit Ihnen.'],
    ['fa-baby', 'Taufe', 'Ein gemütliches Beisammensein nach der Taufe – auch für kleinere Runden.'],
    ['fa-cake-candles', 'Geburtstag &amp; Jubiläum', 'Runde Geburtstage, Goldene Hochzeit oder Vereinsjubiläum – mit Menü oder Buffet.'],
    ['fa-mug-saucer', 'Trauerkaffee', 'Ein ruhiger Rahmen für das Beisammensein nach der Beerdigung – auch kurzfristig nach Absprache.'],
    ['fa-users', 'Firmen- &amp; Vereinsfeiern', 'Weihnachtsfeier, Ausflug oder Stammtisch – sprechen Sie uns gerne an.'],
];
foreach ($items as [$icon, $title, $text]): ?>
    <div class="col-md-6 col-lg-4">
      <article class="info-card info-card--compact h-100">
        <div class="icon-circle icon-circle--sm"><i class="fa-light <?= $icon ?>" aria-hidden="true"></i></div>
        <h3><?= $title ?></h3>
        <p><?= $text ?></p>
      </article>
    </div>
<?php endforeach; ?>
  </div>
</section>

<section class="band">
  <div class="container">
    <header class="section-head">
      <p class="script section-head__script section-head__script--dark">So einfach geht's</p>
      <h2 class="section-head__title">In drei Schritten zur Feier</h2>
    </header>
    <ol class="row g-4 list-unstyled mb-0 steps">
      <li class="col-md-4"><span class="steps__num">1</span><h3>Anfragen</h3><p>Rufen Sie uns an oder senden Sie das Formular mit Datum, Anlass und Personenzahl.</p></li>
      <li class="col-md-4"><span class="steps__num">2</span><h3>Besprechen</h3><p>Gemeinsam stellen wir Menü oder Buffet, Getränke und Tischdeko zusammen.</p></li>
      <li class="col-md-4"><span class="steps__num">3</span><h3>Feiern</h3><p>Sie genießen den Tag mit Ihren Gästen – um alles Weitere kümmern wir uns.</p></li>
    </ol>
    <div class="facts">
      <span><strong>Plätze:</strong> bis [Anzahl] Personen</span>
      <span><strong>Räume:</strong> [Gastraum / Nebenraum / Terrasse]</span>
      <span><strong>Essen:</strong> Menü oder Buffet nach Absprache</span>
    </div>
  </div>
</section>

<section id="anfrage" class="container section">
  <div class="row g-5 align-items-start">
    <div class="col-lg-5 d-flex flex-column gap-3">
      <p class="script page-hero__script mb-0">Ihre Anfrage</p>
      <h2 class="section-title mb-0">Feier unverbindlich anfragen</h2>
      <p class="lead-text mb-0">Erzählen Sie uns kurz, was Sie planen – wir melden uns mit einem Vorschlag bei Ihnen. Am schnellsten erreichen Sie uns telefonisch.</p>
      <p class="mb-0 lh-lg">Telefon: <a href="tel:<?= e($cfg['phone_link']) ?>"><?= e($cfg['phone']) ?></a><br>E-Mail: <a href="mailto:<?= e($cfg['email']) ?>"><?= e($cfg['email']) ?></a></p>
      <img src="<?= e(asset('assets/img/wir-freuen-uns-auf-ihren-besuch.svg')) ?>" alt="Wir freuen uns auf Ihren Besuch!" class="welcome-img welcome-img--xs mt-2" width="360" height="114" loading="lazy">
    </div>
    <div class="col-lg-7">
<?php if ($sent): ?>
      <div class="form-card text-center" role="status">
        <div class="icon-circle icon-circle--lg mx-auto mb-3"><i class="fa-light fa-envelope-circle-check" aria-hidden="true"></i></div>
        <h3 class="mb-2">Vielen Dank für Ihre Anfrage!</h3>
        <p class="mb-0">Wir melden uns so bald wie möglich bei Ihnen.</p>
      </div>
<?php else: ?>
      <form class="form-card" method="post" action="<?= e(url('feierlichkeiten')) ?>#anfrage" novalidate>
<?php if (isset($errors['form'])): ?>
        <div class="alert alert-danger"><?= e($errors['form']) ?></div>
<?php elseif ($errors): ?>
        <div class="alert alert-warning">Bitte prüfen Sie die markierten Felder.</div>
<?php endif; ?>
        <input type="hidden" name="token" value="<?= e(form_token()) ?>">
        <div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label" for="f-name">Name *</label>
            <input class="form-control<?= $inv('name') ?>" id="f-name" type="text" name="name" autocomplete="name" required maxlength="120" value="<?= e($form['name']) ?>">
            <?= $err('name') ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="f-tel">Telefon</label>
            <input class="form-control<?= $inv('tel') ?>" id="f-tel" type="tel" name="tel" autocomplete="tel" maxlength="30" value="<?= e($form['tel']) ?>">
            <?= $err('tel') ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="f-email">E-Mail</label>
            <input class="form-control<?= $inv('email') ?>" id="f-email" type="email" name="email" autocomplete="email" maxlength="120" value="<?= e($form['email']) ?>">
            <?= $err('email') ?>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="f-anlass">Anlass</label>
            <select class="form-select" id="f-anlass" name="anlass">
              <option value="">Bitte wählen</option>
<?php foreach ($occasions as $o): ?>
              <option<?= $form['anlass'] === $o ? ' selected' : '' ?>><?= e($o) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label" for="f-datum">Wunschdatum</label>
            <input class="form-control<?= $inv('datum') ?>" id="f-datum" type="date" name="datum" value="<?= e($form['datum']) ?>">
            <?= $err('datum') ?>
          </div>
          <div class="col-12">
            <label class="form-label" for="f-personen">Anzahl Personen (ca.)</label>
            <input class="form-control<?= $inv('personen') ?>" id="f-personen" type="number" name="personen" min="1" max="999" inputmode="numeric" value="<?= e($form['personen']) ?>">
            <?= $err('personen') ?>
          </div>
          <div class="col-12">
            <label class="form-label" for="f-nachricht">Ihre Nachricht</label>
            <textarea class="form-control<?= $inv('nachricht') ?>" id="f-nachricht" name="nachricht" rows="4" maxlength="5000"><?= e($form['nachricht']) ?></textarea>
            <?= $err('nachricht') ?>
          </div>
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input<?= $inv('datenschutz') ?>" type="checkbox" id="f-ds" name="datenschutz" value="1" required<?= $form['datenschutz'] ? ' checked' : '' ?>>
              <label class="form-check-label small" for="f-ds">Ich habe die <a href="<?= e(url('datenschutz')) ?>">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben einverstanden. *</label>
              <?= $err('datenschutz') ?>
            </div>
          </div>
          <div class="col-12 d-flex flex-wrap align-items-center gap-3">
            <button class="btn btn-cafe" type="submit"><i class="fa-light fa-paper-plane" aria-hidden="true"></i>Anfrage senden</button>
            <span class="small text-muted-green">* Pflichtfelder</span>
          </div>
        </div>
      </form>
<?php endif; ?>
    </div>
  </div>
</section>
<?php require APP . '/layout/footer.php'; ?>
