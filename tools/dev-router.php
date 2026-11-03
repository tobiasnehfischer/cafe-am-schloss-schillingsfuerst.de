<?php
/**
 * Router für den lokalen PHP-Entwicklungsserver (ersetzt public/.htaccess).
 * Im Projektordner starten:
 *   C:\php85\php.exe -S 127.0.0.1:8090 -t public tools/dev-router.php
 * Achtung: /admin/ ist lokal NICHT passwortgeschützt.
 */
$root = dirname(__DIR__) . '/public';
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (preg_match('#^/(app|data)(/|$)#', $path) || preg_match('#/\.#', $path)) {
    http_response_code(403);
    exit('403');
}
if ($path === '/admin') {
    header('Location: /admin/', true, 301);
    exit;
}
if (preg_match('#^/admin/(index\.php)?$#', $path)) {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    require $root . '/admin/index.php';
    return true;
}
if ($path !== '/' && is_file($root . $path) && !preg_match('#\.php$#', $path)) {
    return false; // statische Datei direkt ausliefern
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
return true;
