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

    // Öffnungszeiten (Startseite + Kontakt). Zeilenumbruch mit \n oder <br>, z. B. '11:00 – 14:00 Uhr\n17:00 – 21:00 Uhr'
    'hours' => [
        ['Mo', 'Ruhetag'],
        ['Di – Sa', '11:00 – 15:00 Uhr\n17:00 – 21:00 Uhr'],
        ['Sonntag', '11:00 – 22:00 Uhr'],
    ],
    'hours_note'  => 'Warme Küche bis 20:00 Uhr. An Feiertagen gelten gesonderte Zeiten.',
    // Kurzfassung für den Footer
    'hours_short' => [
        'Di – Sa:\n11:00 – 15:00 Uhr\n17:00 – 21:00 Uhr',
        'So: 11:00 – 22:00 Uhr',
        'Mo: Ruhetag',
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
