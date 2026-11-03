# Café am Schloss – Website

Website für cafe-am-schloss-schillingsfuerst.de. Umgesetzt nach dem Mockup in `claude-mockup/`.
Technik: PHP (ab 7.4, getestet mit 8.5) mit eigenem kleinen Router, Bootstrap 5.3, Font Awesome 7 Pro (Light).
Keine Datenbank und kein Build-Schritt.

## Ordner im Repo

| Ordner | Inhalt | Hochladen? |
|---|---|---|
| `public/` | **Die komplette Website.** Der Inhalt dieses Ordners kommt 1:1 ins Webroot des Webspace. | **ja** |
| `baustelle/` | Baustellenseite (statisches HTML, eigene `.htaccess`) für die Zeit bis zum Livegang. Inhalt ins Webroot laden; beim Livegang komplett durch den Inhalt von `public/` ersetzen. | vorübergehend |
| `quellen/` | Originaldateien: komplettes Bootstrap- und Font-Awesome-Paket, `logo.svg` (CorelDRAW, 900 KB) | nein |
| `claude-mockup/` | HTML-Mockup als Vorlage | nein |
| `tools/` | `dev-router.php` für den lokalen PHP-Server | nein |

## Aufbau von `public/`

| Pfad | Inhalt |
|---|---|
| `index.php` | Front-Controller/Router: schöne URLs, 301 für alte Varianten, 404 |
| `.htaccess` | Leitet alles, was keine echte Datei ist, an `index.php` und sperrt `app/`, `data/` und Dotfiles |
| `app/config.php` | **Stammdaten**: Adresse, Telefon, E-Mail, Öffnungszeiten, Kartenposition, Mail-Empfänger fürs Formular |
| `app/mailer.php` | Mailversand per SMTP (PHPMailer) |
| `app/smtp.php` | SMTP-Zugangsdaten (nicht im Git, Vorlage `smtp.example.php`) |
| `app/content/` | Impressum und Datenschutz (HTML) |
| `app/vendor/` | PHPMailer (Composer) |
| `app/init.php` | Hilfsfunktionen (Inhalte laden/speichern, Hinweis-Logik, Karten) |
| `app/layout/` | Kopf (Navigation, Hinweisleiste) und Fuß |
| `app/pages/` | Seiten: `start`, `speisekarte`, `feierlichkeiten`, `kontakt`, `impressum`, `datenschutz`, `404` |
| `admin/` | Pflegeoberfläche (Passwortschutz per `.htaccess`/`.htpasswd`) |
| `data/` | `content.json` mit den gepflegten Inhalten (legt die Pflegeoberfläche an) |
| `karten/` | Hochgeladene PDFs (`speisekarte.pdf`, `sonntagskarte.pdf`, `biergartenkarte.pdf`) |
| `assets/css/`, `assets/js/` | Eigenes CSS/JS |
| `assets/img/` | Logo (PNG + WebP), Wortmarke, Lilie, „Wir freuen uns …“ (SVG) |
| `assets/fonts/` | Noto Serif (woff2), Abstract Spectrum (ttf) – lokal, ohne Google Fonts |
| `assets/vendor/` | Nur die benötigten Dateien aus Bootstrap 5.3.8 und Font Awesome 7.3.1 Pro (Light) |

Wird ein weiterer Font-Awesome-Stil gebraucht (z. B. `solid`): CSS-Datei und Webfont aus `quellen/fontawesome-pro-7.3.1/`
nach `public/assets/vendor/fontawesome/css/` bzw. `webfonts/` kopieren und in `app/layout/header.php` einbinden.

## Hochladen / Updates

- Erstinstallation: den **Inhalt** von `public/` ins Webroot laden – inklusive der versteckten `.htaccess`- und `.user.ini`-Dateien.
- Bei späteren Updates `data/` und `karten/` auf dem Server **nicht überschreiben oder löschen** – dort liegen die vom Kunden gepflegten Inhalte und PDFs.

## Vorschau unter /dev/ (während die Baustellenseite läuft)

1. Inhalt von `baustelle/` ins Webroot, Inhalt von `public/` nach `/dev/` hochladen (jeweils inkl. Dotfiles).
2. In `/dev/.htaccess` den Block „Vorschau unter /dev/“ einkommentieren und den `.htpasswd`-Pfad eintragen.
   Denselben Pfad in `/dev/admin/.htaccess` eintragen. Ein Login gilt dann für Vorschau und Pflegeoberfläche.
3. Schreibrechte für `/dev/data/` und `/dev/karten/`.

Der Unterordner wird automatisch erkannt (alle Links/Assets bekommen `/dev/` vorangestellt). Falls nicht: in `app/config.php`
`'base_path' => '/dev'` setzen (beim Livegang wieder `null`).

**Livegang:** Baustelle im Webroot löschen, Inhalt von `/dev/` ins Webroot verschieben (inkl. `data/` und `karten/` – dort liegen die
schon gepflegten Inhalte), Vorschau-Block in `.htaccess` wieder auskommentieren, `/dev/` löschen.

## URLs

`/`, `/speisekarte`, `/feierlichkeiten`, `/kontakt`, `/impressum`, `/datenschutz`

Werden mit 301 auf diese Adressen umgeleitet: Endungen `.html`/`.php`, Slash am Ende, Großbuchstaben und
`/index`, `/startseite`, `/speisekarten`, `/karte`, `/anfahrt`, `/feiern`. Neue Seite: Datei in `public/app/pages/` anlegen,
in `public/index.php` bei `$routes` eintragen und in `app/layout/header.php` und `app/layout/footer.php` verlinken.

## Pflegeoberfläche (`/admin/`)

Der Kunde kann pflegen:

- **Hinweis auf der Startseite**: Text, Schalter aktiv/inaktiv, „anzeigen von/bis“ (beide optional, „bis“ gilt einschließlich des ganzen Tages).
  Die grüne Leiste erscheint nur, wenn Text vorhanden, aktiv und heute im Zeitraum ist. Den aktuellen Zustand zeigt die Oberfläche an.
- **Drei PDFs**: Speisekarte, Sonntagskarte, Biergartenkarte. Hochladen ersetzt die alte Datei, „Karte entfernen“ löscht sie.
  Ohne PDF wird der Button bzw. die Karte auf der Website nicht angezeigt. Es werden nur echte PDFs angenommen (max. 20 MB, siehe `app/config.php`).

### Einrichtung auf dem Server

1. `.htpasswd` **außerhalb des Webroots** anlegen: `htpasswd -c -B /pfad/ausserhalb/cafe-am-schloss.htpasswd kunde`
2. Diesen absoluten Pfad in `admin/.htaccess` bei `AuthUserFile` eintragen. Solange dort der Platzhalter steht, liefert `/admin/` einen Serverfehler (Absicht).
3. Schreibrechte für den Webserver-Benutzer auf `data/` und `karten/`.
4. Apache braucht `AllowOverride All` (mindestens `FileInfo AuthConfig Limit Options Indexes`) sowie `mod_rewrite`.
5. Upload-Limits: `admin/.user.ini` gilt für PHP-FPM/CGI. Bei mod_php stattdessen `php_value upload_max_filesize 20M` und `php_value post_max_size 64M` in `admin/.htaccess`.

Zusätzlich sperrt `admin/index.php` den Zugriff, wenn keine HTTP-Anmeldung aktiv ist (z. B. falls `.htaccess`-Dateien ignoriert werden).

## Anfrageformular (Feierlichkeiten)

Versand per **SMTP mit PHPMailer** an `mail_to` aus `app/config.php` (info@cafe-am-schloss-schillingsfuerst.de).
Antworten im Mailprogramm gehen per Reply-To direkt an die E-Mail-Adresse des Anfragenden.

- PHPMailer liegt in `public/app/vendor/` (Composer, `composer.json` im Repo-Root mit `vendor-dir`). Der Ordner wird mit hochgeladen.
  Update: `C:\php85\php.exe C:/ProgramData/ComposerSetup/bin/composer.phar update` im Projektordner.
- Zugangsdaten: `public/app/smtp.example.php` nach `public/app/smtp.php` kopieren und ausfüllen. `smtp.php` ist per
  `.gitignore` ausgeschlossen und liegt in `app/` (per `.htaccess` gesperrt). Ohne `smtp.php` meldet das Formular einen Fehler und nennt die Telefonnummer.
- Test: `C:\php85\php.exe tools/smtp-test.php` prüft Verbindung + Anmeldung ohne Mail, mit `--send` geht eine Testmail an `mail_to`.
- Fehler beim Versand landen im PHP-Error-Log des Servers.
- Spamschutz: Honeypot-Feld und signiertes Zeit-Token (mindestens 3 Sekunden, höchstens 2 Stunden).
- Lokal (`php -S`) wird nichts gesendet. Die Mail landet stattdessen in `public/data/mail-dev.log`.

## Rechtstexte

Quelle: `quellen/impressum.html` und `quellen/datenschutz.html`. Eingebunden aus `public/app/content/*.html` (ohne `<h1>`)
und in `baustelle/impressum.html` bzw. `baustelle/datenschutz.html`. Bei Änderungen beide Stellen aktualisieren.

## Lokal starten

```
C:\php85\php.exe -S 127.0.0.1:8090 -t public tools/dev-router.php
```

im Projektordner ausführen, dann http://127.0.0.1:8090/ öffnen. `tools/dev-router.php` übernimmt die Aufgabe der `.htaccess`. **Lokal ist `/admin/` nicht passwortgeschützt.**

## Noch offen

- [ ] Platzhalter in `[eckigen Klammern]` ersetzen: Öffnungszeiten (`app/config.php`), Zeiten der Sonntagskarte (`app/init.php` → `MENUS`), Anfahrt/Parken (`app/pages/kontakt.php`), Plätze/Räume (`app/pages/feierlichkeiten.php`)
- [ ] Fotos statt der gestrichelten Platzhalter (Startseite, Feierlichkeiten)
- [ ] `public/app/smtp.php` anlegen (SMTP-Zugangsdaten), mit `tools/smtp-test.php` prüfen, Formular auf dem Server testen
- [ ] `.htpasswd` anlegen und `admin/.htaccess` anpassen
- [ ] Logo: `quellen/logo.svg` (900 KB, enthält Rastergrafiken) ist für das Web zu schwer. Verwendet wird `assets/img/logo.webp`/`.png` (690 px). Für Retina-Bildschirme den Export mit ca. 1100 px Breite als PNG liefern lassen.
