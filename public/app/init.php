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

/**
 * Gruppen der PDF-Karten (Reihenfolge = Reihenfolge in der Pflegeoberfläche).
 * 'public' => false: in der Pflegeoberfläche pflegbar, auf der Website (noch) nicht sichtbar.
 */
const MENU_GROUPS = [
    'speisekarte' => [
        'label'  => 'Speisekarte',
        'icon'   => 'fa-utensils',
        'public' => true,
        'hilfe'  => 'Die Speisekarte besteht aus vier Teilen mit je einem PDF. Auf der Website stehen sie gemeinsam unter „Speisekarte“.',
    ],
    'sonntagskarte' => [
        'label'  => 'Sonntagskarte',
        'icon'   => 'fa-calendar-star',
        'public' => true,
        'hilfe'  => '',
    ],
    'biergarten' => [
        'label'  => 'Biergartenkarte',
        'icon'   => 'fa-beer-mug',
        'public' => false, // Biergarten kommt erst später – auf true setzen, sobald die Karte auf die Website soll
        'hilfe'  => 'Wird derzeit nicht auf der Website angezeigt. Das PDF kann trotzdem schon hochgeladen werden.',
    ],
];

/** Die pflegbaren PDF-Karten (Reihenfolge = Anzeige-Reihenfolge). Datei auf dem Server: karten/<schlüssel>.pdf */
const MENUS = [
    'warme-kueche' => [
        'group' => 'speisekarte',
        'label' => 'Warme Küche',
        'icon'  => 'fa-plate-utensils',
        'text'  => 'Suppen, Salate, Kleinigkeiten für den kleinen Hunger und unsere Hauptgerichte.',
    ],
    'vesper' => [
        'group' => 'speisekarte',
        'label' => 'Vesper',
        'icon'  => 'fa-bread-slice',
        'text'  => 'Herzhafte fränkische Schmankerl – Wurstsalat, überbackene Toasts und Bratwürste.',
    ],
    'nachtisch' => [
        'group' => 'speisekarte',
        'label' => 'Nachtisch',
        'icon'  => 'fa-cake-slice',
        'text'  => 'Hofeis aus Geslau sowie hausgemachte Kuchen und Torten.',
    ],
    'getraenke' => [
        'group' => 'speisekarte',
        'label' => 'Getränke',
        'icon'  => 'fa-mug-hot',
        'text'  => 'Bier, alkoholfreie Getränke, Kaffee und Tee, fränkischer Wein und Aperitifs.',
    ],
    'sonntagskarte' => [
        'group' => 'sonntagskarte',
        'label' => 'Sonntagskarte',
        'icon'  => 'fa-calendar-star',
        'text'  => 'Sonntagsbraten und Festtagsgerichte – jeden Sonntag.',
    ],
    'biergartenkarte' => [
        'group' => 'biergarten',
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
        // text null = Startfassung aus app/speisekarte.md
        'menu_text' => ['active' => true, 'text' => null, 'updated' => ''],
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
        'menu_text' => array_merge($defaults['menu_text'], (array) ($data['menu_text'] ?? [])),
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

/**
 * Hochgeladene Karten, die tatsächlich als Datei vorhanden sind.
 * Karten aus Gruppen mit 'public' => false nur mit $includeHidden (Pflegeoberfläche).
 */
function menus_available(array $content, bool $includeHidden = false): array
{
    $out = [];
    foreach (MENUS as $slug => $meta) {
        if (!$includeHidden && empty(MENU_GROUPS[$meta['group']]['public'])) {
            continue;
        }
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

/* ------------------------------------------------------ Online-Speisekarte */

/** Mitgelieferte Startfassung der Speisekarte als Text (solange in der Pflegeoberfläche nichts gespeichert wurde). */
define('MENU_TEXT_DEFAULT', APP . '/speisekarte.md');

/** Text der Online-Speisekarte (gespeicherte Fassung oder Startfassung). */
function menu_text(array $content): string
{
    $text = $content['menu_text']['text'] ?? null;
    if (!is_string($text)) {
        $text = is_file(MENU_TEXT_DEFAULT) ? (string) file_get_contents(MENU_TEXT_DEFAULT) : '';
    }
    return str_replace(["\r\n", "\r"], "\n", $text);
}

/** Online-Speisekarte für die Website ([] wenn ausgeschaltet oder leer). */
function menu_online(): array
{
    $content = content_load();
    return empty($content['menu_text']['active']) ? [] : menu_parse(menu_text($content));
}

/**
 * Speisekarte im einfachen Textformat einlesen:
 *   # Teil            (Warme Küche, Vesper, …)
 *   ## Überschrift    (Unsere Suppen, …)
 *   ### Zwischentitel (z. B. Weingut)
 *   Name | Preis | weiterer Preis   (Gericht; Zeilen direkt darunter = Beschreibung)
 *   sonstige Zeilen = Hinweistext (nach einer Leerzeile)
 * Ergebnis: Teile → Abschnitte → Einträge (item/text/sub).
 */
function menu_parse(string $text): array
{
    $parts = [];
    $p = $c = null;       // Index aktueller Teil / Abschnitt
    unset($last);         // $last = Referenz auf den letzten Eintrag (für Folgezeilen ohne Leerzeile)
    $newPart = static function (string $title) use (&$parts): int {
        $parts[] = ['title' => $title, 'id' => menu_anchor($title), 'intro' => [], 'cats' => []];
        return count($parts) - 1;
    };
    $newCat = static function (int $p, string $title) use (&$parts): int {
        $parts[$p]['cats'][] = ['title' => $title, 'entries' => []];
        return count($parts[$p]['cats']) - 1;
    };

    foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $text)) as $line) {
        $line = trim((string) preg_replace('~^\s*[-*•]\s+~u', '', $line));
        if ($line === '') {
            unset($last);
            continue;
        }
        if (preg_match('~^(#{1,3})\s*(.*)$~u', $line, $m)) {
            unset($last);
            $title = trim($m[2], " #\t");
            $level = strlen($m[1]);
            if ($level === 1) {
                $p = $newPart($title);
                $c = null;
            } elseif ($level === 2) {
                $p = $p ?? $newPart('');
                $c = $newCat($p, $title);
            } elseif ($title !== '') {
                $p = $p ?? $newPart('');
                $c = $c ?? $newCat($p, '');
                $parts[$p]['cats'][$c]['entries'][] = ['type' => 'sub', 'title' => $title];
            }
            continue;
        }
        $p = $p ?? $newPart('');

        // Gericht: „Name | Preis | weiterer Preis“
        if (strpos($line, '|') !== false && trim((string) strstr($line, '|', true)) !== '') {
            unset($last);
            $cells  = array_map('trim', explode('|', $line));
            $name   = array_shift($cells);
            $prices = [];
            foreach ($cells as $cell) {
                if ($cell === '') {
                    continue;
                }
                $cell = menu_price_normalize($cell);
                $prices[] = preg_match('~^(.*?)\s*((?:ab\s+)?\d[\d.]*(?:,\d{1,2})?\s€)$~u', $cell, $pm)
                    ? ['label' => $pm[1], 'amount' => $pm[2]]
                    : ['label' => '', 'amount' => $cell];
            }
            $c = $c ?? $newCat($p, '');
            $parts[$p]['cats'][$c]['entries'][] = ['type' => 'item', 'name' => $name, 'prices' => $prices, 'text' => []];
            $last = &$parts[$p]['cats'][$c]['entries'][count($parts[$p]['cats'][$c]['entries']) - 1];
            continue;
        }

        // Textzeile direkt unter einem Gericht/Text = Fortsetzung, sonst neuer Hinweistext
        if (isset($last)) {
            $last[$last['type'] === 'item' ? 'text' : 'lines'][] = $line;
            continue;
        }
        if ($c === null) {
            $parts[$p]['intro'][] = ['type' => 'text', 'lines' => [$line]];
            $last = &$parts[$p]['intro'][count($parts[$p]['intro']) - 1];
        } else {
            $parts[$p]['cats'][$c]['entries'][] = ['type' => 'text', 'lines' => [$line]];
            $last = &$parts[$p]['cats'][$c]['entries'][count($parts[$p]['cats'][$c]['entries']) - 1];
        }
    }
    unset($last);

    // Teile ohne Inhalt (nur Überschrift) und leere Abschnitte auslassen
    foreach ($parts as $i => $part) {
        $parts[$i]['cats'] = array_values(array_filter($part['cats'], static fn(array $cat): bool => (bool) $cat['entries']));
    }
    return array_values(array_filter($parts, static fn(array $part): bool => $part['cats'] || $part['intro']));
}

/**
 * Abschnitte auf zwei Spalten verteilen (für große Bildschirme): Spalte 1 = die ersten Abschnitte,
 * Spalte 2 = der Rest, Trennstelle so, dass beide Spalten möglichst gleich hoch sind.
 * Höhe grob geschätzt aus Überschrift, Gerichten und Textlänge.
 */
function menu_columns(array $cats): array
{
    if (count($cats) < 2) {
        return [$cats];
    }
    $lines = static fn(array $texts): float => array_sum(array_map(static fn(string $t): float => ceil(mb_strlen($t) / 60), $texts));
    $weights = [];
    foreach ($cats as $cat) {
        $w = 3.0; // Kartenrahmen + Überschrift
        foreach ($cat['entries'] as $en) {
            if ($en['type'] === 'item') {
                $w += 1.3 + max(count($en['prices']) - 1, 0) * 0.6 + $lines($en['text']) * 0.8;
            } elseif ($en['type'] === 'sub') {
                $w += 1.5;
            } else {
                $w += 0.4 + $lines($en['lines']) * 0.8;
            }
        }
        $weights[] = $w;
    }
    $total = array_sum($weights);
    $best  = 1;
    $bestDiff = INF;
    $left = 0.0;
    for ($k = 1; $k < count($cats); $k++) {
        $left += $weights[$k - 1];
        $diff = max($left, $total - $left);
        if ($diff < $bestDiff) {
            $bestDiff = $diff;
            $best = $k;
        }
    }
    return [array_slice($cats, 0, $best), array_slice($cats, $best)];
}

/** „4,80€“ → „4,80 €“, „0,5l“ → „0,5 l“ */
function menu_price_normalize(string $price): string
{
    $price = preg_replace('~(\d)\s*€~u', '$1 €', $price);
    $price = preg_replace('~(\d)\s*(l|cl|ml)\b~u', '$1 $2', $price);
    return trim(preg_replace('~\s+~u', ' ', $price));
}

/** Anker für Überschriften: „Warme Küche“ → „karte-warme-kueche“ */
function menu_anchor(string $title): string
{
    $slug = trim((string) preg_replace('~[^a-z0-9]+~', '-',
        strtr(mb_strtolower($title), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', '&' => 'und'])), '-');
    return 'karte-' . ($slug !== '' ? $slug : 'teil');
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
