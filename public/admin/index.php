<?php
declare(strict_types=1);

/**
 * Pflegeoberfläche – geschützt per .htaccess/.htpasswd (siehe admin/.htaccess).
 * Pflegbar: Hinweistext Startseite (an/aus, von/bis) und drei PDF-Karten.
 */

require dirname(__DIR__) . '/app/init.php';

// Sicherheitsnetz: Ohne aktive HTTP-Anmeldung (z. B. .htaccess wird ignoriert) nicht öffnen.
$authUser = $_SERVER['REMOTE_USER'] ?? $_SERVER['REDIRECT_REMOTE_USER'] ?? $_SERVER['PHP_AUTH_USER'] ?? '';
if (PHP_SAPI !== 'cli-server' && $authUser === '') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Zugriff gesperrt: Der Passwortschutz (admin/.htaccess + .htpasswd) ist nicht aktiv.\n");
}

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

session_name('cafe_admin');
session_set_cookie_params([
    'path'     => url('admin/'),
    'httponly' => true,
    'secure'   => is_https(),
    'samesite' => 'Strict',
]);
session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$cfg      = config();
$maxBytes = (int) $cfg['upload_max_mb'] * 1048576;
$content  = content_load();
$errors   = [];
$form     = $content['notice'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // post_max_size überschritten → PHP liefert leeres $_POST
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'Die Dateien sind zu groß für den Server (insgesamt max. ' . ini_get('post_max_size') . '). Bitte einzeln oder kleiner hochladen.';
    } elseif (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'Die Sitzung ist abgelaufen. Bitte die Seite neu laden und erneut speichern.';
    } else {
        // ---- Hinweis
        $form = [
            'text'   => str_replace("\r\n", "\n", trim((string) ($_POST['notice_text'] ?? ''))),
            'active' => !empty($_POST['notice_active']),
            'from'   => trim((string) ($_POST['notice_from'] ?? '')),
            'until'  => trim((string) ($_POST['notice_until'] ?? '')),
        ];
        if (!mb_check_encoding($form['text'], 'UTF-8')) {
            $errors[] = 'Der Hinweistext enthält ungültige Zeichen.';
            $form['text'] = mb_convert_encoding($form['text'], 'UTF-8', 'UTF-8');
        } elseif (mb_strlen($form['text']) > 600) {
            $errors[] = 'Der Hinweistext ist zu lang (max. 600 Zeichen).';
        }
        foreach (['from' => 'Anzeigen von', 'until' => 'Anzeigen bis'] as $key => $label) {
            if ($form[$key] !== '' && !DateTimeImmutable::createFromFormat('!Y-m-d', $form[$key])) {
                $errors[] = "„{$label}“ ist kein gültiges Datum.";
            }
        }
        if (!$errors && $form['from'] !== '' && $form['until'] !== '' && $form['from'] > $form['until']) {
            $errors[] = '„Anzeigen bis“ darf nicht vor „Anzeigen von“ liegen.';
        }
        if ($form['active'] && $form['text'] === '') {
            $errors[] = 'Bitte einen Hinweistext eingeben oder den Hinweis ausschalten.';
        }

        // ---- PDFs prüfen (erst alles prüfen, dann speichern)
        $uploads = [];
        foreach (MENUS as $slug => $meta) {
            $file = $_FILES['pdf_' . $slug] ?? null;
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $label = $meta['label'];
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > $maxBytes) {
                $errors[] = "{$label}: Die Datei ist zu groß (max. {$cfg['upload_max_mb']} MB).";
                continue;
            }
            if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                $errors[] = "{$label}: Upload fehlgeschlagen (Fehlercode {$file['error']}).";
                continue;
            }
            $head = (string) file_get_contents($file['tmp_name'], false, null, 0, 5);
            if ($head !== '%PDF-' || strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $errors[] = "{$label}: Bitte nur PDF-Dateien hochladen.";
                continue;
            }
            $uploads[$slug] = $file['tmp_name'];
        }

        if (!$errors) {
            try {
                if (!is_dir(MENU_DIR) && !mkdir(MENU_DIR, 0775, true)) {
                    throw new RuntimeException('Ordner karten/ kann nicht angelegt werden.');
                }
                $messages = [];
                foreach (MENUS as $slug => $meta) {
                    $target = MENU_DIR . '/' . $slug . '.pdf';
                    if (isset($uploads[$slug])) {
                        if (!move_uploaded_file($uploads[$slug], $target)) {
                            throw new RuntimeException($meta['label'] . ': Datei konnte nicht gespeichert werden (Schreibrechte für karten/ prüfen).');
                        }
                        @chmod($target, 0644);
                        $content['menus'][$slug] = ['url' => 'karten/' . $slug . '.pdf', 'updated' => date('Y-m-d H:i:s')];
                        $messages[] = $meta['label'] . ' hochgeladen.';
                    } elseif (!empty($_POST['remove_' . $slug]) && $content['menus'][$slug] !== null) {
                        if (is_file($target)) {
                            @unlink($target);
                        }
                        $content['menus'][$slug] = null;
                        $messages[] = $meta['label'] . ' entfernt.';
                    }
                }
                $content['notice'] = $form;
                content_save($content);
                $_SESSION['flash'] = array_merge(['Änderungen gespeichert.'], $messages);
                redirect(url('admin/'));
            } catch (RuntimeException $ex) {
                $errors[] = $ex->getMessage();
            }
        }
    }
}

$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

$status = notice_status(['notice' => $form] + $content);
$menus  = menus_available($content);
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Pflege – <?= e($cfg['site_name']) ?></title>
<link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/lilie.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/fontawesome/css/fontawesome.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/vendor/fontawesome/css/light.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
<style>
  body { --bs-body-font-size: 1rem; }
  .admin-card { background: var(--cafe-surface); border: 1px solid var(--cafe-border); border-radius: 16px; padding: 1.75rem; }
  .admin-card h2 { font-size: 1.35rem; font-weight: 600; letter-spacing: .04em; display: flex; align-items: center; gap: .6rem; margin-bottom: 1.25rem; }
  .admin-card h2 i { color: var(--cafe-gold); }
  .menu-row + .menu-row { border-top: 1px solid var(--cafe-border); padding-top: 1.25rem; margin-top: 1.25rem; }
  .form-switch .form-check-input { width: 3em; height: 1.6em; margin-top: 0; cursor: pointer; }
  .form-switch .form-check-label { padding-left: .75rem; cursor: pointer; font-size: 1.05rem; }
  .status { border-radius: 10px; padding: .6rem .9rem; font-size: .95rem; }
  .status--on { background: #E3EDD9; color: #24361E; }
  .status--off { background: #F3E8CC; color: #6B5A35; }
  .form-control[type=file] { min-height: auto; padding: .6rem .75rem; }
  .help { font-size: .875rem; color: var(--cafe-muted); }
</style>
</head>
<body>
<header class="site-header">
  <div class="container d-flex align-items-center justify-content-between py-3 gap-3">
    <div class="d-flex align-items-center gap-3">
      <img src="<?= e(asset('assets/img/logo-text-only.svg')) ?>" alt="<?= e($cfg['site_name']) ?>" height="52" width="91">
      <span class="fs-5 ls-wide d-none d-sm-inline">Pflege</span>
    </div>
    <a class="btn btn-cafe-outline btn-sm" href="<?= e(url()) ?>" target="_blank" rel="noopener"><i class="fa-light fa-arrow-up-right-from-square" aria-hidden="true"></i>Website ansehen</a>
  </div>
</header>

<main class="container py-4 py-md-5" style="max-width: 860px">
<?php if ($flash): ?>
  <div class="alert alert-success d-flex gap-2" role="status">
    <i class="fa-light fa-circle-check mt-1" aria-hidden="true"></i>
    <div><?= implode('<br>', array_map('e', $flash)) ?></div>
  </div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <strong>Nicht gespeichert:</strong>
    <ul class="mb-0 mt-1"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="d-flex flex-column gap-4">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

    <section class="admin-card">
      <h2><i class="fa-light fa-circle-info" aria-hidden="true"></i>Hinweis auf der Startseite</h2>
      <div class="mb-3">
        <label class="form-label" for="notice_text">Hinweistext</label>
        <textarea class="form-control" id="notice_text" name="notice_text" rows="3" maxlength="600" placeholder="z. B. Betriebsurlaub: Vom 20.12. bis 06.01. bleibt unser Café geschlossen."><?= e($form['text']) ?></textarea>
        <div class="help mt-1">Erscheint als grüne Leiste ganz oben auf der Startseite.</div>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="notice_active" name="notice_active" value="1"<?= $form['active'] ? ' checked' : '' ?>>
        <label class="form-check-label" for="notice_active">Hinweis aktiv</label>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label" for="notice_from">Anzeigen von <span class="help">(optional)</span></label>
          <input class="form-control" type="date" id="notice_from" name="notice_from" value="<?= e($form['from']) ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="notice_until">Anzeigen bis einschließlich <span class="help">(optional)</span></label>
          <input class="form-control" type="date" id="notice_until" name="notice_until" value="<?= e($form['until']) ?>">
        </div>
      </div>
      <div class="status <?= $status['visible'] ? 'status--on' : 'status--off' ?>">
        <i class="fa-light <?= $status['visible'] ? 'fa-eye' : 'fa-eye-slash' ?> me-1" aria-hidden="true"></i>
        <strong>Aktuell:</strong> <?= e($status['reason']) ?>
      </div>
    </section>

    <section class="admin-card">
      <h2><i class="fa-light fa-file-pdf" aria-hidden="true"></i>Speisekarten (PDF)</h2>
      <p class="help mb-4">Für jede Karte kann ein PDF hochgeladen werden (max. <?= (int) $cfg['upload_max_mb'] ?> MB). Ein neues PDF ersetzt das alte. Ist keine Datei hinterlegt, wird der Button auf der Website nicht angezeigt.</p>
<?php foreach (MENUS as $slug => $meta): $cur = $menus[$slug] ?? null; ?>
      <div class="menu-row">
        <label class="form-label fw-semibold" for="pdf_<?= e($slug) ?>"><?= e($meta['label']) ?></label>
<?php if ($cur): ?>
        <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 mb-2">
          <a href="<?= e($cur['url']) ?>" target="_blank" rel="noopener"><i class="fa-light fa-file-pdf me-1" aria-hidden="true"></i>Aktuelle Datei ansehen</a>
          <span class="help">hochgeladen am <?= e(format_date($cur['updated'])) ?> · <?= e(format_size($cur['size'])) ?></span>
          <div class="form-check ms-sm-auto mb-0">
            <input class="form-check-input" type="checkbox" id="remove_<?= e($slug) ?>" name="remove_<?= e($slug) ?>" value="1">
            <label class="form-check-label" for="remove_<?= e($slug) ?>">Karte entfernen</label>
          </div>
        </div>
<?php else: ?>
        <p class="help mb-2"><i class="fa-light fa-eye-slash me-1" aria-hidden="true"></i>Keine Datei hinterlegt – wird auf der Website nicht angezeigt.</p>
<?php endif; ?>
        <input class="form-control" type="file" id="pdf_<?= e($slug) ?>" name="pdf_<?= e($slug) ?>" accept="application/pdf,.pdf">
      </div>
<?php endforeach; ?>
    </section>

    <div class="d-flex justify-content-end">
      <button class="btn btn-cafe" type="submit"><i class="fa-light fa-floppy-disk" aria-hidden="true"></i>Speichern</button>
    </div>
  </form>
</main>
</body>
</html>
