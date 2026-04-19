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

---

## Zeichen-Canvas fuer Schaltplaene

Wenn SuS einen Schaltplan oder eine Skizze zeichnen sollen, NIEMALS einen leeren Kasten verwenden.
Stattdessen immer diesen interaktiven Canvas einbauen:

```html
<!-- Zeichen-Canvas fuer Schaltplan / Skizze -->
<h3 style="color:[PHASENFARBE];">Aufgabe X: Schaltplan skizzieren</h3>
<p>[AUFGABENTEXT]</p>

<div class="canvas-toolbar" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">
  <label style="font-size:0.85em;font-weight:600;color:#555;">Farbe:</label>
  <input type="color" id="penColor" value="#000000" style="width:36px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;padding:2px;">
  <label style="font-size:0.85em;font-weight:600;color:#555;">Groesse:</label>
  <input type="range" id="penSize" min="1" max="20" value="2" style="width:80px;">
  <span id="penSizeLabel" style="font-size:0.85em;color:#555;">2px</span>
  <button onclick="setEraser(false)" id="btnPen" style="padding:6px 12px;border:2px solid [PHASENFARBE];border-radius:6px;background:[PHASENFARBE];color:#fff;cursor:pointer;font-size:0.85em;">&#9998; Stift</button>
  <button onclick="setEraser(true)" id="btnEraser" style="padding:6px 12px;border:2px solid #ccc;border-radius:6px;background:#fff;color:#333;cursor:pointer;font-size:0.85em;">&#9746; Radierer</button>
  <button onclick="clearCanvas()" style="padding:6px 12px;border:2px solid #e53935;border-radius:6px;background:#fff;color:#e53935;cursor:pointer;font-size:0.85em;">&#128465; Leeren</button>
  <button onclick="downloadCanvas()" style="padding:6px 12px;border:2px solid #2E7D32;border-radius:6px;background:#2E7D32;color:#fff;cursor:pointer;font-size:0.85em;">&#128229; Als PNG speichern</button>
</div>
<style>@media print { .canvas-toolbar { display:none !important; } }</style>

<canvas id="schaltplanCanvas" width="800" height="300"
  style="border:2px solid [PHASENFARBE];border-radius:8px;cursor:crosshair;background:#fff;width:100%;touch-action:none;display:block;">
</canvas>

<script>
(function() {
  const canvas = document.getElementById('schaltplanCanvas');
  const ctx = canvas.getContext('2d');
  let drawing = false, erasing = false, lastX = 0, lastY = 0;

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    if (e.touches) {
      return { x: (e.touches[0].clientX - rect.left) * scaleX, y: (e.touches[0].clientY - rect.top) * scaleY };
    }
    return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY };
  }

  function startDraw(e) { e.preventDefault(); drawing = true; const p = getPos(e); lastX = p.x; lastY = p.y; }
  function draw(e) {
    e.preventDefault(); if (!drawing) return;
    const p = getPos(e);
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(p.x, p.y);
    ctx.strokeStyle = erasing ? '#ffffff' : document.getElementById('penColor').value;
    ctx.lineWidth = erasing ? document.getElementById('penSize').value * 4 : document.getElementById('penSize').value;
    ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.stroke();
    lastX = p.x; lastY = p.y;
  }
  function stopDraw(e) { e.preventDefault(); drawing = false; }

  canvas.addEventListener('mousedown', startDraw); canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', stopDraw); canvas.addEventListener('mouseleave', stopDraw);
  canvas.addEventListener('touchstart', startDraw, {passive:false}); canvas.addEventListener('touchmove', draw, {passive:false});
  canvas.addEventListener('touchend', stopDraw, {passive:false});

  document.getElementById('penSize').addEventListener('input', function() {
    document.getElementById('penSizeLabel').textContent = this.value + 'px';
  });

  window.setEraser = function(val) {
    erasing = val;
    document.getElementById('btnPen').style.background = val ? '#fff' : '[PHASENFARBE]';
    document.getElementById('btnPen').style.color = val ? '#333' : '#fff';
    document.getElementById('btnEraser').style.background = val ? '[PHASENFARBE]' : '#fff';
    document.getElementById('btnEraser').style.color = val ? '#fff' : '#333';
  };
  window.clearCanvas = function() { if (confirm('Zeichnung loeschen?')) ctx.clearRect(0, 0, canvas.width, canvas.height); };
  window.downloadCanvas = function() { const l = document.createElement('a'); l.download = 'schaltplan.png'; l.href = canvas.toDataURL('image/png'); l.click(); };
})();
</script>

<p style="font-size:0.85em;color:#666;margin-top:8px;">
  &#128161; Tipp: Zeichne deinen Schaltplan direkt oben (auch per Touch/Stift auf Tablet),
  oder skizziere ihn auf Papier und lade ein Foto hoch.
  Mit "Als PNG speichern" kannst du die digitale Zeichnung als Datei herunterladen.
</p>
```

Ersetze [PHASENFARBE] durch die Farbe der aktuellen Phase (z.B. #6A1B9A fuer Phase 2).

---

## Universeller Zeichen-Canvas

Immer wenn SuS etwas zeichnen, skizzieren oder diagrammatisch darstellen sollen,
NIEMALS einen leeren Kasten verwenden. Stattdessen diesen Canvas einsetzen.

Einsatzbeispiele:
- Schaltplan (Elektronik)
- UML-Diagramm (Klassen, Sequenz, Use-Case)
- Wireframe / Mockup (UI-Design)
- Netzwerktopologie
- Flussdiagramm / Ablaufplan
- Mindmap
- ER-Diagramm
- Freihand-Skizze jeder Art

Parameter zum Anpassen:
- CANVAS_ID: eindeutige ID pro Seite (z.B. "schaltplan", "uml", "wireframe")
- CANVAS_HOEHE: Hoehe in px (300 = Standard, 500 = gross, 200 = klein)
- PHASENFARBE: Rahmenfarbe passend zur Phase
- AUFGABENTEXT: Beschreibung was gezeichnet werden soll
- DATEINAME: Dateiname beim Download (z.B. "schaltplan.png", "uml-diagramm.png")

```html
<!-- Universeller Zeichen-Canvas -->
<h3 style="color:[PHASENFARBE];">Aufgabe X: [AUFGABENTEXT]</h3>

<div id="toolbar_[CANVAS_ID]" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap;">

  <!-- Werkzeuge -->
  <button onclick="setTool_[CANVAS_ID]('pen')" id="btn_pen_[CANVAS_ID]"
    style="padding:6px 12px;border:2px solid [PHASENFARBE];border-radius:6px;background:[PHASENFARBE];color:#fff;cursor:pointer;font-size:0.85em;">
    &#9998; Stift
  </button>
  <button onclick="setTool_[CANVAS_ID]('line')" id="btn_line_[CANVAS_ID]"
    style="padding:6px 12px;border:2px solid #555;border-radius:6px;background:#fff;color:#333;cursor:pointer;font-size:0.85em;">
    &#9135; Linie
  </button>
  <button onclick="setTool_[CANVAS_ID]('rect')" id="btn_rect_[CANVAS_ID]"
    style="padding:6px 12px;border:2px solid #555;border-radius:6px;background:#fff;color:#333;cursor:pointer;font-size:0.85em;">
    &#9645; Rechteck
  </button>
  <button onclick="setTool_[CANVAS_ID]('eraser')" id="btn_eraser_[CANVAS_ID]"
    style="padding:6px 12px;border:2px solid #555;border-radius:6px;background:#fff;color:#333;cursor:pointer;font-size:0.85em;">
    &#9746; Radierer
  </button>

  <!-- Trennlinie -->
  <span style="color:#ccc;">|</span>

  <!-- Farbe und Groesse -->
  <label style="font-size:0.85em;font-weight:600;color:#555;">Farbe:</label>
  <input type="color" id="color_[CANVAS_ID]" value="#000000"
    style="width:32px;height:28px;border:1px solid #ccc;border-radius:4px;cursor:pointer;padding:2px;">

  <label style="font-size:0.85em;font-weight:600;color:#555;">Groesse:</label>
  <input type="range" id="size_[CANVAS_ID]" min="1" max="20" value="2" style="width:70px;">
  <span id="sizelabel_[CANVAS_ID]" style="font-size:0.85em;color:#555;">2px</span>

  <!-- Trennlinie -->
  <span style="color:#ccc;">|</span>

  <!-- Aktionen -->
  <button onclick="undoCanvas_[CANVAS_ID]()"
    style="padding:6px 12px;border:2px solid #1565C0;border-radius:6px;background:#fff;color:#1565C0;cursor:pointer;font-size:0.85em;">
    &#8617; Undo
  </button>
  <button onclick="clearCanvas_[CANVAS_ID]()"
    style="padding:6px 12px;border:2px solid #e53935;border-radius:6px;background:#fff;color:#e53935;cursor:pointer;font-size:0.85em;">
    &#128465; Leeren
  </button>
  <button onclick="downloadCanvas_[CANVAS_ID]()"
    style="padding:6px 12px;border:2px solid #2E7D32;border-radius:6px;background:#2E7D32;color:#fff;cursor:pointer;font-size:0.85em;">
    &#128229; Als PNG speichern
  </button>

</div>
<style>@media print { #toolbar_[CANVAS_ID] { display:none !important; } }</style>

<canvas id="canvas_[CANVAS_ID]" width="900" height="[CANVAS_HOEHE]"
  style="border:2px solid [PHASENFARBE];border-radius:8px;cursor:crosshair;background:#fff;width:100%;touch-action:none;display:block;">
</canvas>

<script>
(function() {
  const canvas = document.getElementById('canvas_[CANVAS_ID]');
  const ctx = canvas.getContext('2d');
  const FARBE = '[PHASENFARBE]';
  let tool = 'pen';
  let drawing = false;
  let startX = 0, startY = 0, lastX = 0, lastY = 0;
  let history = [];
  let snapshot = null;

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const sx = canvas.width / rect.width;
    const sy = canvas.height / rect.height;
    const src = e.touches ? e.touches[0] : e;
    return { x: (src.clientX - rect.left) * sx, y: (src.clientY - rect.top) * sy };
  }

  function saveHistory() {
    history.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
    if (history.length > 30) history.shift();
  }

  function startDraw(e) {
    e.preventDefault();
    drawing = true;
    const p = getPos(e);
    startX = lastX = p.x;
    startY = lastY = p.y;
    saveHistory();
    if (tool === 'line' || tool === 'rect') {
      snapshot = ctx.getImageData(0, 0, canvas.width, canvas.height);
    }
  }

  function draw(e) {
    e.preventDefault();
    if (!drawing) return;
    const p = getPos(e);
    const color = document.getElementById('color_[CANVAS_ID]').value;
    const size = parseInt(document.getElementById('size_[CANVAS_ID]').value);

    if (tool === 'pen') {
      ctx.beginPath();
      ctx.moveTo(lastX, lastY);
      ctx.lineTo(p.x, p.y);
      ctx.strokeStyle = color;
      ctx.lineWidth = size;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.stroke();
      lastX = p.x; lastY = p.y;
    } else if (tool === 'eraser') {
      ctx.beginPath();
      ctx.moveTo(lastX, lastY);
      ctx.lineTo(p.x, p.y);
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = size * 4;
      ctx.lineCap = 'round';
      ctx.stroke();
      lastX = p.x; lastY = p.y;
    } else if (tool === 'line') {
      ctx.putImageData(snapshot, 0, 0);
      ctx.beginPath();
      ctx.moveTo(startX, startY);
      ctx.lineTo(p.x, p.y);
      ctx.strokeStyle = color;
      ctx.lineWidth = size;
      ctx.lineCap = 'round';
      ctx.stroke();
    } else if (tool === 'rect') {
      ctx.putImageData(snapshot, 0, 0);
      ctx.beginPath();
      ctx.strokeStyle = color;
      ctx.lineWidth = size;
      ctx.strokeRect(startX, startY, p.x - startX, p.y - startY);
    }
  }

  function stopDraw(e) { e.preventDefault(); drawing = false; snapshot = null; }

  canvas.addEventListener('mousedown', startDraw);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', stopDraw);
  canvas.addEventListener('mouseleave', stopDraw);
  canvas.addEventListener('touchstart', startDraw, { passive: false });
  canvas.addEventListener('touchmove', draw, { passive: false });
  canvas.addEventListener('touchend', stopDraw, { passive: false });

  document.getElementById('size_[CANVAS_ID]').addEventListener('input', function() {
    document.getElementById('sizelabel_[CANVAS_ID]').textContent = this.value + 'px';
  });

  function updateButtons(active) {
    ['pen','line','rect','eraser'].forEach(function(t) {
      const btn = document.getElementById('btn_' + t + '_[CANVAS_ID]');
      if (!btn) return;
      btn.style.background = (t === active) ? FARBE : '#fff';
      btn.style.color = (t === active) ? '#fff' : '#333';
      btn.style.borderColor = (t === active) ? FARBE : '#555';
    });
    canvas.style.cursor = (active === 'eraser') ? 'cell' : 'crosshair';
  }

  window['setTool_[CANVAS_ID]'] = function(t) { tool = t; updateButtons(t); };
  window['undoCanvas_[CANVAS_ID]'] = function() {
    if (history.length > 0) ctx.putImageData(history.pop(), 0, 0);
  };
  window['clearCanvas_[CANVAS_ID]'] = function() {
    if (confirm('Zeichnung loeschen?')) {
      saveHistory();
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
  };
  window['downloadCanvas_[CANVAS_ID]'] = function() {
    const link = document.createElement('a');
    link.download = '[DATEINAME]';
    link.href = canvas.toDataURL('image/png');
    link.click();
  };

  updateButtons('pen');
})();
</script>

<p style="font-size:0.85em;color:#666;margin-top:8px;">
  &#128161; Tipp: Nutze Stift zum Freihand-Zeichnen, Linie und Rechteck fuer praezise Formen.
  Mit "Undo" kannst du den letzten Schritt rueckgaengig machen (bis zu 30 Schritte).
  Mit "Als PNG speichern" laedt die Zeichnung als Datei herunter – diese dann in Moodle hochladen.
  Alternativ: Skizze auf Papier, Foto machen und hochladen.
</p>
```

Werkzeuge des Canvas:
- Stift: Freihand-Zeichnen (fuer organische Formen, Skizzen)
- Linie: Gerade Verbindungslinie (fuer Schaltplaene, UML-Verbindungen)
- Rechteck: Knoeten, Bloecke, Komponenten (fuer UML, ER, Wireframes)
- Radierer: Korrekturen (4x Stiftgroesse)
- Undo: Bis zu 30 Schritte zurueck
- PNG speichern: Download der fertigen Zeichnung zur Abgabe

Mehrere Canvas auf einer Seite: Jeder Canvas braucht eine eindeutige CANVAS_ID!
Z.B. "diagramm1", "diagramm2" – alle window-Funktionen sind dann automatisch getrennt.
