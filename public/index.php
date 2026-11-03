<?php
declare(strict_types=1);

/**
 * Front-Controller: alle Anfragen ohne passende Datei landen hier (.htaccess).
 * Schöne URLs: /speisekarte, /kontakt … Alte Varianten (.html/.php, Slash am Ende) → 301.
 */

require __DIR__ . '/app/init.php';

$routes = [
    ''                => 'start',
    'speisekarte'     => 'speisekarte',
    'feierlichkeiten' => 'feierlichkeiten',
    'kontakt'         => 'kontakt',
    'impressum'       => 'impressum',
    'datenschutz'     => 'datenschutz',
];

// Alte bzw. naheliegende Adressen, die dauerhaft umgeleitet werden
$aliases = [
    'index'        => '',
    'startseite'   => '',
    'home'         => '',
    'speisekarten' => 'speisekarte',
    'karte'        => 'speisekarte',
    'anfahrt'      => 'kontakt',
    'feiern'       => 'feierlichkeiten',
];

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$base = base_path();
if ($base !== '' && strncasecmp($path, $base, strlen($base)) === 0) {
    $path = substr($path, strlen($base));
}
$path = '/' . ltrim($path, '/');

$slug = strtolower(trim($path, '/'));
$slug = (string) preg_replace('/\.(html?|php)$/', '', $slug);
$slug = $aliases[$slug] ?? $slug;

if (!array_key_exists($slug, $routes)) {
    http_response_code(404);
    render('404');
    exit;
}

$canonical = '/' . $slug;
if ($path !== $canonical) {
    $query = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    redirect(url($slug) . ($query !== '' ? '?' . $query : ''), 301);
}

render($routes[$slug], ['currentSlug' => $slug]);
