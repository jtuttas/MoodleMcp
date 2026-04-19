---
name: moodle-lernsituationen-creator
description: >
  Erstellt vollständige Moodle-Kursabschnitte für handlungsorientierte Lernsituationen an berufsbildenden Schulen (BBS) via Moodle MCP Server. Der Skill baut den gesamten Kurs automatisch auf: Abschnittstitel, Handlungssituation als gestaltete Card, alle 5 Handlungsphasen als farbkodierte Label-Header, Textseiten als Informationsblätter mit Syntax-Highlighting, Links/URLs zu Fachdokumentationen sowie Aufgaben mit PDF-Button und Abgabe-Hinweis. Verwende diesen Skill IMMER wenn der Benutzer sagt: "erstelle einen Moodle-Kurs", "baue den Kurs auf", "Moodle-Kurs für die Lernsituation", "lege die LS in Moodle an", "Moodle-Abschnitt erstellen", "Kurs befüllen", oder wenn eine Lernsituation (als Datei oder Text) vorliegt und eine Kurs-ID genannt wird.
---

# Skill: Moodle-Lernsituationen-Creator (MCP-Version)

Erstellt einen vollständigen, didaktisch hochwertigen Moodle-Kursabschnitt auf Basis einer
handlungsorientierten Lernsituation. Nutzt ausschließlich den **Moodle MCP Server** –
kein Browser, keine Klicks, direkte API-Calls.

---

## Verfügbare MCP-Tools

| Tool | Verwendung |
|---|---|
| `moodle_get_sections` | Abschnitte eines Kurses lesen |
| `moodle_get_modules` | Aktivitäten + cmids eines Abschnitts lesen |
| `moodle_update_section` | Abschnittsname + Handlungssituation-Card setzen |
| `moodle_create_label` | Phasen-Header anlegen (direkt auf Kursseite sichtbar) |
| `moodle_create_page` | Textseite anlegen (nur lesen, mit Syntax-Highlighting) |
| `moodle_create_url` | Externen Link anlegen |
| `moodle_create_assign` | Aufgabe anlegen (mit PDF-Button + Abgabe-Hinweis) |
| `moodle_update_label` | Label bearbeiten |
| `moodle_update_page` | Textseite bearbeiten |
| `moodle_update_assign` | Aufgabe bearbeiten |
| `moodle_update_url` | Link bearbeiten |

---

## Workflow

### Schritt 1: Kursstruktur prüfen

```
moodle_get_sections(courseid=KURS_ID)
```

Freien Abschnitt identifizieren und Abschnittsnummer notieren.

### Schritt 2: Abschnitt benennen + Handlungssituation setzen

```
moodle_update_section(
  courseid=KURS_ID,
  sectionnum=N,
  name="LS X.Y - [Titel]",
  summary=[HANDLUNGSSITUATION_HTML]
)
```

### Schritt 3: Pro Phase - Label + Aktivitäten

Für jede der 5 Phasen in dieser Reihenfolge:
1. `moodle_create_label` - farbiger Phasen-Header
2. `moodle_create_page` - Informationsblatt (nur lesen)
3. `moodle_create_url` - externe Links (falls vorhanden)
4. `moodle_create_assign` - Aufgaben (wenn SuS etwas abgeben)

---

## HTML-Vorlagen

### Handlungssituation-Card (für update_section summary)

```html
<div style="background:linear-gradient(135deg,#1a237e,#283593);border-radius:12px;padding:0;margin-bottom:20px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.2);">
  <div style="background:rgba(255,255,255,0.1);padding:12px 20px;display:flex;align-items:center;gap:10px;">
    <span style="font-size:1.4em;">&#127919;</span>
    <div>
      <div style="color:rgba(255,255,255,0.7);font-size:0.75em;font-weight:600;letter-spacing:2px;text-transform:uppercase;">LERNSITUATION X.Y - FIRMENNAME</div>
      <div style="color:#fff;font-size:1.1em;font-weight:700;">Handlungssituation</div>
    </div>
  </div>
  <div style="background:#fff;margin:0 16px 16px;border-radius:8px;padding:20px;">
    <p style="color:#333;line-height:1.7;margin-bottom:16px;">[SITUATIONSBESCHREIBUNG]</p>
    <div style="border-top:2px solid #e8eaf6;padding-top:14px;">
      <div style="color:#1a237e;font-size:0.75em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px;">&#127919; HANDLUNGSERGEBNISSE</div>
      <ul style="margin:0;padding-left:20px;color:#444;line-height:2;">
        <li>[ERGEBNIS 1]</li>
        <li>[ERGEBNIS 2]</li>
        <li>[ERGEBNIS 3]</li>
      </ul>
    </div>
  </div>
</div>
```

### Phasen-Header (für moodle_create_label content)

Ersetze FARBE, PHASE_NR, PHASE_NAME, ICON_CODE, ZEIT, SOZIALFORM:

```html
<div style="background:linear-gradient(135deg,[FARBE]dd,[FARBE]);border-radius:10px;padding:16px 20px;margin:10px 0;box-shadow:0 3px 10px rgba(0,0,0,0.15);">
  <div style="display:flex;align-items:center;gap:14px;">
    <span style="font-size:2em;">[ICON_CODE]</span>
    <div>
      <div style="color:rgba(255,255,255,0.8);font-size:0.7em;font-weight:700;letter-spacing:2px;text-transform:uppercase;">PHASE [PHASE_NR]</div>
      <div style="color:#fff;font-size:1.25em;font-weight:700;">[PHASE_NAME]</div>
      <div style="color:rgba(255,255,255,0.85);font-size:0.82em;margin-top:3px;">&#9203; ca. [ZEIT] Minuten &nbsp;•&nbsp; [SOZIALFORM]</div>
    </div>
  </div>
</div>
```

Farbschema und Icons der 5 Phasen:

| Phase | Name | Farbe | Icon-Code |
|---|---|---|---|
| 1 | Informieren / Analysieren | #1565C0 | &#128269; |
| 2 | Planen / Entscheiden | #6A1B9A | &#128203; |
| 3 | Durchfuehren | #E65100 | &#9881;&#65039; |
| 4 | Kontrollieren / Bewerten | #2E7D32 | &#9989; |
| 5 | Reflektieren | #00695C | &#128172; |

### Textseite mit Syntax-Highlighting (für moodle_create_page content)

Fuer Seiten MIT Codebeispielen - highlight.js IMMER einbinden:

```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/ini.min.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ hljs.highlightAll(); });</script>

<div style="font-family:Arial,sans-serif;max-width:900px;margin:0 auto;padding:20px;">
  <h2 style="color:[PHASENFARBE];border-bottom:3px solid [PHASENFARBE];padding-bottom:8px;">[TITEL]</h2>

  <div style="background:[PHASENFARBE_HELL];border-left:4px solid [PHASENFARBE];padding:16px;border-radius:4px;margin-bottom:24px;">
    <strong>Lernziel:</strong> [LERNZIEL]
  </div>

  <h3 style="color:[PHASENFARBE];">[ABSCHNITTSTITEL]</h3>
  <p>[ERKLAERUNGSTEXT]</p>

  <pre><code class="language-cpp">
// Codebeispiel hier
void setup() { }
void loop() { }
  </code></pre>

</div>
```

Verfuegbare Sprachen:
language-cpp, language-python, language-javascript, language-java,
language-bash, language-ini, language-json, language-html, language-css

Fuer Seiten OHNE Code kann der highlight.js-Block weggelassen werden.

### Aufgabe mit PDF-Button (fuer moodle_create_assign description)

PFLICHT bei allen Aufgaben - immer PDF-Banner oben und Abgabe-Hinweis unten:

```html
<div style="font-family:Arial,sans-serif;padding:20px;">

  <div style="background:linear-gradient(135deg,#1a237e,#283593);border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:12px;">
      <span style="font-size:1.8em;">&#128221;</span>
      <div>
        <div style="color:#fff;font-weight:700;font-size:1em;">Arbeitsblatt zum Ausdrucken oder digital ausfuellen</div>
        <div style="color:rgba(255,255,255,0.8);font-size:0.85em;margin-top:2px;">Fuelle das Blatt aus und lade es als PDF oder Foto ueber "Einreichen" hoch.</div>
      </div>
    </div>
    <button onclick="window.print()" style="background:#fff;color:#1a237e;border:none;border-radius:8px;padding:10px 20px;font-weight:700;font-size:0.95em;cursor:pointer;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,0.2);">&#128438; Als PDF speichern</button>
  </div>
  <style>@media print { button { display:none !important; } }</style>

  <div style="background:[PHASENFARBE_HELL];border-left:4px solid [PHASENFARBE];padding:16px;border-radius:4px;margin-bottom:24px;">
    <strong>Arbeitsauftrag:</strong> [AUFGABENSTELLUNG]
  </div>

  [AUFGABEN_INHALT_MIT_INPUTS]

  <div style="background:#E8F5E9;border-left:4px solid #2E7D32;padding:14px 16px;border-radius:4px;margin-top:20px;">
    <strong style="color:#2E7D32;">&#128229; Abgabe:</strong>
    <span style="color:#333;"> Klicke auf "Als PDF speichern" (oben), oder mache ein Foto deiner handschriftlichen Loesung. Lade die Datei anschliessend ueber den Button "Einreichen" in Moodle hoch.</span>
  </div>

</div>
```

---

## Goldene Regel: Aktivitaetstyp waehlen

| Situation | Tool |
|---|---|
| SuS liest nur (Infoblatt, Leitfaden, Anleitung, Code-Beispiel) | moodle_create_page |
| SuS fuellt etwas aus / gibt etwas ab / reflektiert | moodle_create_assign |
| Phasen-Header (direkt auf Kursseite sichtbar) | moodle_create_label |
| Externe Dokumentation, GitHub, MDN | moodle_create_url |

NIEMALS ein Arbeitsblatt als Textseite anlegen!

---

## Interaktive Elemente in Aufgaben

```html
<!-- Texteingabe kurz -->
<input type="text" style="width:90%;padding:4px;border:1px solid #bbb;border-radius:4px;" placeholder="Hinweis..."/>

<!-- Texteingabe lang -->
<textarea style="width:100%;border:1px solid #bbb;border-radius:4px;padding:8px;font-family:Arial;font-size:14px;" rows="3" placeholder="Deine Antwort..."></textarea>

<!-- Checkbox (IMMER echtes input, NIEMALS &#9744;!) -->
<input type="checkbox" style="width:20px;height:20px;cursor:pointer;accent-color:#2E7D32;"/>

<!-- Radio-Bewertungsskala (pro Zeile eigenen name-Wert!) -->
<input type="radio" name="bewertung_1" value="1"/> 1 &nbsp;
<input type="radio" name="bewertung_1" value="2"/> 2 &nbsp;
<input type="radio" name="bewertung_1" value="3"/> 3
```

---

## Wichtige Hinweise

- KEINE Emojis in Titeln (name-Feld) - Moodle-DB kein UTF8MB4. Im HTML-Content HTML-Entities verwenden
- Kein draftfile.php - immer CDN-URLs oder HTML-Entities
- SuS fuellt etwas aus -> IMMER moodle_create_assign mit PDF-Button
- Code in Textseiten -> IMMER highlight.js mit <pre><code class="language-XY">
- Abschnittsnummer ist 0-basiert: Abschnitt 1 = sectionnum: 1
- Nach jedem Tool-Aufruf kurz Fortschritt berichten

---

## Schnell-Checkliste

```
moodle_update_section    -> Abschnittsname + Handlungssituation-Card
moodle_create_label      -> Phase 1: Informieren / Analysieren (blau #1565C0)
moodle_create_page       -> Informationsblatt [+highlight.js falls Code]
moodle_create_url        -> Dokumentation / Referenz-Links
moodle_create_label      -> Phase 2: Planen / Entscheiden (lila #6A1B9A)
moodle_create_assign     -> Arbeitsblatt [MIT PDF-Button]
moodle_create_label      -> Phase 3: Durchfuehren (orange #E65100)
moodle_create_page       -> Implementierungsleitfaden [+highlight.js]
moodle_create_assign     -> Hauptaufgabe formativ [MIT PDF-Button]
moodle_create_label      -> Phase 4: Kontrollieren / Bewerten (gruen #2E7D32)
moodle_create_assign     -> Testprotokoll / Checkliste [MIT PDF-Button]
moodle_create_label      -> Phase 5: Reflektieren (teal #00695C)
moodle_create_assign     -> Reflexion [MIT PDF-Button]
moodle_create_assign     -> Abschlusspraesentation summativ [MIT PDF-Button]
```
