# Café am Schloss – Website (Mockup als HTML)

Statisches HTML-Mockup der Website. Alle Seiten funktionieren direkt im Browser (`index.html` öffnen), sind responsiv und teilen Header, Navigation und Footer.

## Seiten

| Datei | Inhalt |
|---|---|
| `index.html` | Startseite: optionaler Hinweis (z. B. Betriebsurlaub), Begrüßung, Anschrift & Kontakt, Öffnungszeiten, Speisekarten (PDF), Feierlichkeiten-Teaser, Kontakt & Anfahrt (`#kontakt`) |
| `speisekarten.html` | Speisekarte und Sonntagskarte als PDF (Ansehen / Herunterladen) |
| `feierlichkeiten.html` | Anlässe, Ablauf, Anfrageformular |
| `impressum.html` | Impressum (Platzhalter) |
| `datenschutz.html` | Datenschutzerklärung (Platzhalter-Gliederung) |

## Design

- **Farben:** Hintergrund `#FBF4E2`, Flächen `#FDF8EC`, Band/Footer `#F6E3B8`, Text/Buttons Dunkelgrün `#1F2E1A` / `#24361E`, Gold `#B07E22` (Linien), Gold-Text `#7A5410`
- **Schriften:** Noto Serif (Fließtext, Überschriften) und **Abstract Spectrum** (Handschrift, Klasse `.script`). Abstract Spectrum ist keine freie Webschrift – bitte die Lizenz/Webfont-Datei des Kunden einbinden; im Mockup springt bis dahin „Kaushan Script“ ein.
- **Grafiken:** `assets/` (Logo, Wortmarke, Lilie, „Wir freuen uns auf Ihren Besuch!“). Für die finale Seite am besten die Originaldateien in höherer Auflösung bzw. als SVG verwenden.

## Noch zu erledigen

- [ ] Platzhalter in eckigen Klammern ersetzen: Öffnungszeiten, E-Mail, Anfahrt/Parken, Fotos, Stand der Karten
- [ ] PDFs in `pdf/speisekarte.pdf` und `pdf/sonntagskarte.pdf` ablegen
- [ ] Hinweis-Leiste oben auf der Startseite (Betriebsurlaub) ein-/ausblendbar machen, z. B. über das CMS
- [ ] Schriften **lokal** einbinden (DSGVO) statt über Google Fonts – der `fonts.googleapis.com`-Link ist nur fürs Mockup
- [ ] Karte unter „Kontakt & Anfahrt“ einbauen (OpenStreetMap oder Google Maps mit Zwei-Klick-Lösung)
- [ ] Anfrageformular auf „Feierlichkeiten“ an Mailversand/Backend anbinden (inkl. Spamschutz)
- [ ] Impressum und Datenschutzerklärung mit geprüften Texten füllen
- [ ] Navigation ist für weitere Seiten vorbereitet – neue Menüpunkte einfach in `<nav class="nav">` ergänzen (alle Seiten)
