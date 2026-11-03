<?php
/**
 * Stammdaten der Website. Werte in [eckigen Klammern] sind Platzhalter.
 */
return [
    'site_name'  => 'Café am Schloss',

    // Für canonical-Links (ohne abschließenden Slash). Leer lassen = kein canonical.
    'base_url'   => 'https://www.cafe-am-schloss-schillingsfuerst.de',

    // Unterordner, falls die Seite nicht im Webroot liegt (z. B. '/cafe'). null = automatisch.
    'base_path'  => null,

    'street'     => 'Am Wall 10',
    'zip'        => '91583',
    'city'       => 'Schillingsfürst',
    'phone'      => '09868 9344244',
    'phone_link' => '+4998689344244',
    'email'      => 'info@cafe-am-schloss-schillingsfuerst.de',

    // Öffnungszeiten (Startseite + Kontakt)
    'hours' => [
        ['Mo – Di', 'Ruhetag'],
        ['Mi – Fr', '[hh:mm] – [hh:mm] Uhr'],
        ['Samstag', '[hh:mm] – [hh:mm] Uhr'],
        ['Sonntag', '[hh:mm] – [hh:mm] Uhr'],
    ],
    'hours_note'  => 'Warme Küche bis [hh:mm] Uhr. An Feiertagen gelten gesonderte Zeiten.',
    // Kurzfassung für den Footer
    'hours_short' => [
        'Mi – Sa: [hh:mm] – [hh:mm]',
        'So: [hh:mm] – [hh:mm]',
        'Mo – Di: Ruhetag',
    ],

    // Kartenposition (OpenStreetMap, Zwei-Klick-Lösung)
    'map_lat' => 49.28967,
    'map_lon' => 10.26054,

    // Anfrageformular „Feierlichkeiten“: Empfänger. Versand per SMTP, Zugangsdaten in app/smtp.php (Vorlage: smtp.example.php)
    // 'mail_to'   => 'info@cafe-am-schloss-schillingsfuerst.de',
    'mail_to'   => 't.nehfischer@shw-komplett.de',

    // Pflegeoberfläche: maximale PDF-Größe in MB (Server-Limits ggf. in admin/.user.ini anpassen)
    'upload_max_mb' => 20,
];
