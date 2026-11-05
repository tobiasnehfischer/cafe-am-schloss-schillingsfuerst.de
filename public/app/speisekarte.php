<?php
/**
 * Online-Speisekarte (Seite /speisekarte).
 *
 * - Reihenfolge der Kategorien und Gerichte = Reihenfolge auf der Seite.
 * - 'preis': Zahl (12.9 → „12,90 €“) oder Text (z. B. 'ab 3,80 €', 'Tagespreis').
 * - 'merkmale' (optional): 'vegetarisch', 'vegan'.
 * - 'hinweis' (optional, je Kategorie): kurzer Satz unter der Überschrift.
 * - 'icon': Font-Awesome-Name (Stil „light“).
 *
 * 'anzeigen' => false blendet die Online-Speisekarte komplett aus (dann nur PDF-Karten).
 * 'beispiel' => true blendet auf der Seite einen Hinweis „Beispieldaten“ ein.
 * Nach dem Eintragen der echten Karte 'beispiel' auf false und 'anzeigen' auf true setzen.
 */
return [
    'anzeigen' => false,
    'beispiel' => true,

    'fussnote' => 'Alle Preise in Euro inkl. gesetzlicher Mehrwertsteuer.',

    'kategorien' => [
        [
            'titel'    => 'Suppen',
            'icon'     => 'fa-bowl-hot',
            'gerichte' => [
                ['name' => 'Leberknödelsuppe', 'text' => 'Kräftige Rinderbrühe mit hausgemachtem Leberknödel und Schnittlauch', 'preis' => 5.5],
                ['name' => 'Flädlesuppe', 'text' => 'Rinderbrühe mit feinen Pfannkuchenstreifen', 'preis' => 4.9],
                ['name' => 'Tagessuppe', 'text' => 'Fragen Sie unser Service-Team', 'preis' => 4.9, 'merkmale' => ['vegetarisch']],
            ],
        ],
        [
            'titel'    => 'Brotzeiten',
            'icon'     => 'fa-sausage',
            'gerichte' => [
                ['name' => '6 Nürnberger Rostbratwürste', 'text' => 'Mit Sauerkraut und Bauernbrot', 'preis' => 11.5],
                ['name' => 'Obatzda', 'text' => 'Mit Zwiebeln, Radieschen und frischer Brezen', 'preis' => 9.5, 'merkmale' => ['vegetarisch']],
                ['name' => 'Fränkischer Wurstsalat', 'text' => 'Sauer angemacht mit Zwiebeln, dazu Bauernbrot', 'preis' => 10.5],
                ['name' => 'Fränkische Brotzeitplatte', 'text' => 'Schinken, Presssack, Leberwurst und Käse, dazu Butter und Bauernbrot', 'preis' => 14.9],
            ],
        ],
        [
            'titel'    => 'Fränkische Klassiker',
            'icon'     => 'fa-plate-utensils',
            'hinweis'  => 'Unsere Klöße sind hausgemacht.',
            'gerichte' => [
                ['name' => 'Fränkisches Schäufele', 'text' => 'Mit Kartoffelkloß, Dunkelbiersoße und Krautsalat', 'preis' => 18.9],
                ['name' => 'Sauerbraten', 'text' => 'Mit Lebkuchensoße, Rotkohl und Kartoffelkloß', 'preis' => 17.5],
                ['name' => 'Schweinebraten', 'text' => 'Mit Kartoffelkloß und Krautsalat', 'preis' => 15.9],
                ['name' => 'Schnitzel Wiener Art', 'text' => 'Vom Schwein, mit Bratkartoffeln und Beilagensalat', 'preis' => 16.5],
                ['name' => 'Kinderschnitzel', 'text' => 'Mit Pommes frites', 'preis' => 8.9],
            ],
        ],
        [
            'titel'    => 'Vegetarisch & Salate',
            'icon'     => 'fa-salad',
            'gerichte' => [
                ['name' => 'Käsespätzle', 'text' => 'Mit Bergkäse und Röstzwiebeln, dazu Beilagensalat', 'preis' => 13.5, 'merkmale' => ['vegetarisch']],
                ['name' => 'Gemüsepfanne', 'text' => 'Saisonales Gemüse mit Kräuterkartoffeln', 'preis' => 13.9, 'merkmale' => ['vegan']],
                ['name' => 'Großer Salatteller', 'text' => 'Bunte Blattsalate mit gebratenen Putenstreifen und Brot', 'preis' => 14.5],
                ['name' => 'Beilagensalat', 'text' => '', 'preis' => 4.5, 'merkmale' => ['vegan']],
            ],
        ],
        [
            'titel'    => 'Desserts',
            'icon'     => 'fa-ice-cream',
            'gerichte' => [
                ['name' => 'Apfelstrudel', 'text' => 'Warm, mit Vanillesoße', 'preis' => 6.5, 'merkmale' => ['vegetarisch']],
                ['name' => 'Gemischtes Eis', 'text' => 'Drei Kugeln mit Sahne', 'preis' => 5.9, 'merkmale' => ['vegetarisch']],
                ['name' => 'Rote Grütze', 'text' => 'Mit Vanilleeis', 'preis' => 6.2, 'merkmale' => ['vegetarisch']],
            ],
        ],
        [
            'titel'    => 'Kaffee & Kuchen',
            'icon'     => 'fa-mug-hot',
            'hinweis'  => 'Unsere hausgemachten Kuchen und Torten finden Sie in der Kuchentheke.',
            'gerichte' => [
                ['name' => 'Tasse Kaffee', 'text' => '', 'preis' => 3.2],
                ['name' => 'Cappuccino', 'text' => '', 'preis' => 3.9],
                ['name' => 'Latte macchiato', 'text' => '', 'preis' => 4.2],
                ['name' => 'Kännchen Tee', 'text' => 'Verschiedene Sorten', 'preis' => 4.5],
                ['name' => 'Stück Kuchen oder Torte', 'text' => 'Mit Sahne + 0,80 €', 'preis' => 'ab 3,80 €'],
            ],
        ],
    ],
];
