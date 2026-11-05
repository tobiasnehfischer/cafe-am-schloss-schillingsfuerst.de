<?php
declare(strict_types=1);

/**
 * Gemeinsame Grundlage für Website (index.php) und Pflegeoberfläche (admin/).
 * Keine Datenbank: Inhalte liegen in data/content.json, PDFs in karten/.
 */

date_default_timezone_set('Europe/Berlin');
mb_internal_encoding('UTF-8');

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
define('DATA_DIR', ROOT . '/data');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('MENU_DIR', ROOT . '/karten');

/** Die drei pflegbaren Karten (Reihenfolge = Anzeige-Reihenfolge). */
const MENUS = [
    'speisekarte' => [
        'label' => 'Speisekarte',
        'icon'  => 'fa-utensils',
        'text'  => 'Fränkische Klassiker, saisonale Gerichte, Kaffee und hausgemachter Kuchen.',
    ],
    'sonntagskarte' => [
        'label' => 'Sonntagskarte',
        'icon'  => 'fa-calendar-star',
        'text'  => 'Sonntagsbraten und Festtagsgerichte – jeden Sonntag.',
    ],
    'biergartenkarte' => [
        'label' => 'Biergartenkarte',
        'icon'  => 'fa-beer-mug',
        'text'  => 'Brotzeiten, kühle Getränke und leichte Gerichte für warme Tage in unserem Biergarten.',
    ],
];

function config(?string $key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require APP . '/config.php';
    }
    return $key === null ? $cfg : ($cfg[$key] ?? null);
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Text mit Zeilenumbrüchen sicher ausgeben. Als Umbruch gelten: echter Zeilenumbruch,
 * die Zeichenfolge \n (auch in '…'-Strings), <br> sowie ein Array mit mehreren Zeilen.
 */
function e_lines($value): string
{
    $lines = is_array($value)
        ? $value
        : preg_split('~\r\n|\r|\n|\\\\n|<br\s*/?>~i', (string) $value);
    return implode('<br>', array_map('e', array_map('trim', $lines)));
}

/** URL-Präfix der Website ('' im Webroot, sonst z. B. '/cafe'). */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $configured = config('base_path');
    if (is_string($configured)) {
        return $base = rtrim($configured, '/');
    }
    $norm = static fn(string $p): string => str_replace('\\', '/', $p);
    $root = $norm(realpath(ROOT) ?: ROOT);

    // 1) Aus dem aufgerufenen Skript ableiten: /dev/admin/index.php minus /admin/index.php = /dev
    //    (unabhängig von DOCUMENT_ROOT, das bei Symlinks/Hosting-Panels oft abweicht)
    $scriptName = $norm((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptFile = $norm(realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) ?: '');
    if ($scriptName !== '' && $scriptFile !== '' && stripos($scriptFile, $root . '/') === 0) {
        $rel = substr($scriptFile, strlen($root));
        if (strcasecmp(substr($scriptName, -strlen($rel)), $rel) === 0) {
            return $base = rtrim(substr($scriptName, 0, -strlen($rel)), '/');
        }
    }

    // 2) Fallback: Projektordner relativ zu DOCUMENT_ROOT
    $docRoot = $norm(realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $base    = '';
    if ($docRoot !== '' && stripos($root, $docRoot) === 0) {
        $base = substr($root, strlen($docRoot));
    }
    return $base = rtrim($base, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Asset-URL mit Cache-Busting über die Änderungszeit. */
function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $url  = url(str_replace(' ', '%20', $path));
    return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function redirect(string $location, int $status = 303): void
{
    header('Location: ' . $location, true, $status);
    exit;
}

/* ------------------------------------------------------------------ Inhalte */

function content_defaults(): array
{
    $menus = [];
    foreach (array_keys(MENUS) as $slug) {
        $menus[$slug] = null;
    }
    return [
        'notice' => ['text' => '', 'active' => false, 'from' => '', 'until' => ''],
        'menus'  => $menus,
    ];
}

function content_load(): array
{
    $data = [];
    if (is_file(CONTENT_FILE)) {
        $decoded = json_decode((string) file_get_contents(CONTENT_FILE), true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
    $defaults = content_defaults();
    return [
        'notice' => array_merge($defaults['notice'], (array) ($data['notice'] ?? [])),
        'menus'  => array_merge($defaults['menus'], array_intersect_key((array) ($data['menus'] ?? []), $defaults['menus'])),
    ];
}

/** Schreibt atomar (temporäre Datei + rename), damit nie eine halbe JSON-Datei entsteht. */
function content_save(array $content): void
{
    if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0775, true)) {
        throw new RuntimeException('Ordner data/ kann nicht angelegt werden.');
    }
    $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    $tmp  = CONTENT_FILE . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if ($json === false || file_put_contents($tmp, $json . "\n", LOCK_EX) === false || !rename($tmp, CONTENT_FILE)) {
        @unlink($tmp);
        throw new RuntimeException('Inhalte konnten nicht gespeichert werden (Schreibrechte für data/ prüfen).');
    }
}

/**
 * Status des Hinweises: ['visible' => bool, 'reason' => string].
 * Zeitraum: „von“ ab 00:00 Uhr, „bis“ einschließlich des ganzen Tages.
 */
function notice_status(array $content, ?DateTimeImmutable $now = null): array
{
    $n     = $content['notice'];
    $today = ($now ?? new DateTimeImmutable('now'))->format('Y-m-d');
    $fmt   = static fn(string $d): string => DateTimeImmutable::createFromFormat('!Y-m-d', $d)->format('d.m.Y');

    if (trim((string) $n['text']) === '') {
        return ['visible' => false, 'reason' => 'Kein Hinweistext eingetragen.'];
    }
    if (!$n['active']) {
        return ['visible' => false, 'reason' => 'Der Hinweis ist ausgeschaltet.'];
    }
    if ($n['from'] !== '' && $today < $n['from']) {
        return ['visible' => false, 'reason' => 'Wird ab ' . $fmt($n['from']) . ' angezeigt.'];
    }
    if ($n['until'] !== '' && $today > $n['until']) {
        return ['visible' => false, 'reason' => 'Anzeigezeitraum ist am ' . $fmt($n['until']) . ' abgelaufen.'];
    }
    $range = $n['until'] !== '' ? ' (bis einschließlich ' . $fmt($n['until']) . ')' : '';
    return ['visible' => true, 'reason' => 'Wird auf allen Seiten angezeigt' . $range . '.'];
}

/** Aktuell sichtbarer Hinweistext (leer, wenn keiner angezeigt wird). */
function notice_text(): string
{
    $content = content_load();
    return notice_status($content)['visible'] ? trim((string) $content['notice']['text']) : '';
}

/** Hochgeladene Karten, die tatsächlich als Datei vorhanden sind. */
function menus_available(array $content): array
{
    $out = [];
    foreach (MENUS as $slug => $meta) {
        $entry = $content['menus'][$slug] ?? null;
        if (!is_array($entry) || empty($entry['url'])) {
            continue;
        }
        $file = ROOT . '/' . ltrim($entry['url'], '/');
        if (!is_file($file)) {
            continue;
        }
        $out[$slug] = $meta + [
            'url'     => url($entry['url']) . '?v=' . filemtime($file),
            'updated' => $entry['updated'] ?? '',
            'size'    => filesize($file),
        ];
    }
    return $out;
}

/** Online-Speisekarte aus app/speisekarte.php (Kategorien ohne Gerichte werden ausgelassen; 'anzeigen' => false = keine). */
function menu_card(): array
{
    $file = APP . '/speisekarte.php';
    $data = is_file($file) ? require $file : [];
    $data = is_array($data) ? $data : [];
    $data['kategorien'] = empty($data['anzeigen']) ? [] : array_values(array_filter(
        (array) ($data['kategorien'] ?? []),
        static fn($k): bool => is_array($k) && !empty($k['gerichte'])
    ));
    return $data + ['beispiel' => false, 'fussnote' => ''];
}

/** 12.9 → „12,90 €“; Text (z. B. „ab 3,80 €“) bleibt unverändert. */
function format_price($price): string
{
    if (is_int($price) || is_float($price)) {
        return number_format((float) $price, 2, ',', '.') . ' €';
    }
    return trim((string) $price);
}

function format_date(string $value): string
{
    $ts = strtotime($value);
    return $ts ? date('d.m.Y', $ts) : '';
}

function format_size(int $bytes): string
{
    return $bytes >= 1048576
        ? number_format($bytes / 1048576, 1, ',', '.') . ' MB'
        : max(1, (int) round($bytes / 1024)) . ' KB';
}

/* ------------------------------------------------------- Formular-Schutz */

/** Geheimer Schlüssel für Formular-Tokens (wird beim ersten Aufruf erzeugt). */
function app_secret(): string
{
    $file = DATA_DIR . '/secret.key';
    if (!is_file($file)) {
        if (!is_dir(DATA_DIR)) {
            @mkdir(DATA_DIR, 0775, true);
        }
        @file_put_contents($file, bin2hex(random_bytes(32)), LOCK_EX);
    }
    $secret = is_file($file) ? trim((string) file_get_contents($file)) : '';
    return $secret !== '' ? $secret : hash('sha256', ROOT . php_uname());
}

function form_token(): string
{
    $t = (string) time();
    return $t . '.' . hash_hmac('sha256', $t, app_secret());
}

/** Gültig, wenn signiert, mindestens 3 Sekunden und höchstens 2 Stunden alt. */
function form_token_valid(string $token): bool
{
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return false;
    }
    if (!hash_equals(hash_hmac('sha256', $parts[0], app_secret()), $parts[1])) {
        return false;
    }
    $age = time() - (int) $parts[0];
    return $age >= 3 && $age <= 7200;
}

/* ------------------------------------------------------------- Ausgabe */

function render(string $page, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require APP . '/pages/' . $page . '.php';
}

/** Ornament: Linie – Lilie – Linie */
function divider(string $class = ''): string
{
    return '<div class="divider ' . e($class) . '" aria-hidden="true"><span></span><img src="'
        . e(asset('assets/img/lilie.svg')) . '" alt="" width="34" height="31"><span></span></div>';
}
