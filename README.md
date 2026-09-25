# moodle-mcp

KI-gestützter Moodle-Kursaufbau via Claude Desktop und MCP.

Claude Desktop spricht direkt mit der Moodle REST API und kann Kursabschnitte,
Textseiten, Labels, Aufgaben und externe Links anlegen und bearbeiten.

Zusätzlich kann der Server lokal erzeugte Dateien (z.B. PDF/DOCX/XLSX) als
"Zusätzliche Dateien" direkt in Moodle-Aufgaben hochladen.

Außerdem unterstützt der Server das Setzen von **Abschlussverfolgung** und
**Voraussetzungen/Verfügbarkeit** (Aktivität ist gesperrt, bis andere Aktivitäten
abgeschlossen sind).

```
Claude Desktop (stdio)
       |
  moodle-mcp.js          <- lokaler MCP Server (Node.js)
       |
  Moodle REST API        <- local_aicoursecreator Plugin
       |
  Moodle 4.x
```

---

## Voraussetzungen

- Moodle 4.0 oder neuer
- Node.js (v18+) auf dem Rechner mit Claude Desktop
- Claude Desktop
- Admin-Zugang zu Moodle

---

## Installation

### 1. Moodle-Plugin installieren

Das Plugin `local_aicoursecreator` stellt die benötigten Webservice-Funktionen bereit.

1. `local_aicoursecreator.zip` herunterladen
   (im Repository liegt die ZIP unter `Plugin/local_aicoursecreator.zip`)
2. In Moodle: **Website-Administration → Plugins → Plugin installieren**
3. ZIP hochladen und Upgrade bestätigen

> **Hinweis:** Falls du das Plugin neu installierst (Update), zuerst deinstallieren,
> dann die neue ZIP installieren.

### 2. Web Services in Moodle aktivieren

**Website-Administration → Erweiterte Funktionen:**
- "Webservices aktivieren" ✅

**Website-Administration → Plugins → Webservices → Protokolle verwalten:**
- REST-Protokoll aktivieren (Auge-Symbol) ✅

### 3. API-Token erstellen

**Website-Administration → Server → Webservices → Token verwalten → Token hinzufügen**

- **Nutzer:** Admin oder Lehrer mit Kursbearbeitungsrechten
- **Dienst:** `AI Course Creator Service`
- Token kopieren – er wird nur einmal angezeigt!

> **Sicherheit:** Den Token wie ein Passwort behandeln. Niemals in
> öffentliche Repositories oder Chats einfügen.

### 4. moodle-mcp.js einrichten

`moodle-mcp.js` auf den lokalen Rechner kopieren, z.B. nach:
- Windows: `C:\moodle-mcp\moodle-mcp.js`
- macOS/Linux: `~/moodle-mcp/moodle-mcp.js`

Keine weiteren Abhängigkeiten – nur Node.js wird benötigt.

### 5. Claude Desktop konfigurieren

Konfigurationsdatei öffnen:
- Windows: `%APPDATA%\Claude\claude_desktop_config.json`
- macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`

Folgenden Inhalt eintragen:

```json
{
  "mcpServers": {
    "moodle": {
      "command": "node",
      "args": ["C:\\moodle-mcp\\moodle-mcp.js"],
      "env": {
        "MOODLE_URL": "https://deine-moodle-url/moodle",
        "MOODLE_TOKEN": "dein-api-token"
      }
    }
  }
}
```

Auf macOS/Linux:

```json
{
  "mcpServers": {
    "moodle": {
      "command": "node",
      "args": ["/home/user/moodle-mcp/moodle-mcp.js"],
      "env": {
        "MOODLE_URL": "https://deine-moodle-url/moodle",
        "MOODLE_TOKEN": "dein-api-token"
      }
    }
  }
}
```

### 6. Claude Desktop neu starten

Claude Desktop vollständig beenden (auch aus dem System-Tray) und neu starten.
Unten links das Hammer-Symbol prüfen – dort sollten die Moodle-Tools erscheinen.

---

## Verfügbare Tools

| Tool | Beschreibung |
|---|---|
| `moodle_get_courses` | Kurse suchen/listen, wenn die Kurs-ID noch unbekannt ist |
| `moodle_get_sections` | Alle Abschnitte eines Kurses lesen |
| `moodle_create_section` | Kursabschnitt anlegen und optional benennen/beschreiben |
| `moodle_get_modules` | Alle Aktivitäten eines Abschnitts mit cmid lesen |
| `moodle_get_assign_content` | Gespeicherte HTML-Beschreibung und Metadaten einer Aufgabe rein lesend über die cmid abrufen |
| `moodle_get_label_content` | Gespeicherten HTML-Inhalt und Metadaten eines Text- und Medienfelds rein lesend über die cmid abrufen |
| `moodle_get_page_content` | Gespeicherten HTML-Inhalt und Metadaten einer Textseite rein lesend über die cmid abrufen |
| `moodle_update_section` | Abschnittsname und Beschreibung setzen |
| `moodle_create_label` | Text- und Medienfeld anlegen (Phasen-Header) |
| `moodle_update_label` | Text- und Medienfeld bearbeiten |
| `moodle_create_page` | Textseite anlegen |
| `moodle_update_page` | Textseite bearbeiten |
| `moodle_create_url` | Externen Link anlegen |
| `moodle_update_url` | Externen Link bearbeiten |
| `moodle_create_assign` | Aufgabe anlegen |
| `moodle_update_assign` | Aufgabe bearbeiten |
| `moodle_upload_assignfile` | Datei als "Zusätzliche Datei" in eine Aufgabe hochladen |
| `moodle_set_completion` | Abschlussverfolgung für eine Aktivität konfigurieren |
| `moodle_set_restriction` | Aktivität sperren, bis andere Aktivitäten abgeschlossen sind |
| `moodle_set_module_visibility` | Aktivität per cmid ein- oder ausblenden |
| `moodle_delete_module` | Aktivität per cmid löschen |
| `moodle_get_question_categories` | Fragenkategorien eines Kurses lesen |
| `moodle_get_question_types` | Installierte/verfügbare Moodle-Fragetypen lesen |
| `moodle_create_question_category` | Fragenkategorie in der Kurs-Fragensammlung anlegen |
| `moodle_import_questions_xml` | Fragen aus Moodle-XML in eine Kategorie importieren |
| `moodle_create_quiz` | Quiz-Aktivität in einem Kursabschnitt anlegen |
| `moodle_add_quiz_questions` | Importierte/vorhandene Fragen einem Quiz hinzufügen |
| `moodle_update_quiz` | Bestehendes Quiz bearbeiten |

### Sichtbarkeit (optional)

Viele Create/Update-Tools unterstützen den Parameter `visible`:

- `1` = sichtbar
- `0` = versteckt
- bei Update-Tools zusätzlich: `-1` = nicht ändern (Standard)

---

## KI-Skill: Lernsituationen automatisch aufbauen

Das Projekt enthält einen Claude-Skill in [`SKILL.md`](SKILL.md), der Claude anweist,
aus einer handlungsorientierten Lernsituation automatisch einen vollständigen
Moodle-Kursabschnitt zu erstellen – ohne Browser, ohne manuelle Klicks.

**Trigger-Phrasen für Claude:**
- "Erstelle einen Moodle-Kurs für diese Lernsituation"
- "Baue den Kurs in Moodle auf"
- "Lege die LS in Moodle an (Kurs-ID: ...)"

Der Skill übernimmt dabei automatisch:
- Abschnittsname + gestaltete Handlungssituations-Card
- Alle 5 Handlungsphasen als farbkodierte Label-Header
- Informationsblätter als Textseiten (mit Syntax-Highlighting für Code)
- Externe Dokumentationslinks
- Aufgaben mit PDF-Druckbutton und Abgabe-Hinweis

Technische Details, HTML-Vorlagen und Entscheidungsregeln für den Aktivitätstyp
sind vollständig in [`SKILL.md`](SKILL.md) dokumentiert.

---

## Verwendung

### Kurs-ID herausfinden

Die Kurs-ID steht in der URL des Moodle-Kurses:

```
https://moodle.example.de/moodle/course/view.php?id=42
                                                    ^^
                                               das ist die ID
```

### Beispiele

**Abschnitte eines Kurses lesen:**
> "Lies die Abschnitte von Kurs 42"

**Kurs-ID suchen:**
> "Suche Moodle-Kurse mit dem Namen ESP32"

**Neuen Abschnitt anlegen:**
> "Lege in Kurs 42 Abschnitt 3 als 'LS 7.3 - Sensorik' an und füge eine kurze HTML-Beschreibung hinzu."

**Einen Abschnitt benennen:**
> "Benenne Abschnitt 1 in Kurs 42 als 'LS 7.2 – ESP32 Webserver'"

**Komplette Lernsituation aufbauen:**
> "Baue in Kurs 42, Abschnitt 2 eine Lernsituation zum Thema ESP32 Webserver auf.
> Firma ist TechSolutions GmbH. Erstelle alle 5 Handlungsphasen mit passenden
> Aktivitäten."

**Bestehende Seite bearbeiten:**
> "Lies die Module in Abschnitt 1 von Kurs 42 und ändere den Inhalt der
> Textseite 'Informationsblatt' auf einen aktualisierten Text."

**Abschlussverfolgung aktivieren (für Voraussetzungen):**
> "Lies die Module in Abschnitt 2 von Kurs 42. Aktiviere für die Aufgabe 'Arbeitsblatt' die Abschlussverfolgung: automatisch bei Einreichung."

**Aktivität sperren bis andere abgeschlossen sind:**
> "Sperre in Kurs 42 die Textseite 'Implementierung' bis die Aufgabe 'Konzept-Abgabe' abgeschlossen ist. Zeige die gesperrte Aktivität ausgegraut an."

**Datei in eine Aufgabe hochladen:**
> "Lies die Module in Abschnitt 2 von Kurs 42, finde die Aufgabe 'Arbeitsblatt' und lade die Datei `C:\\temp\\Arbeitsblatt.pdf` als zusätzliche Datei in diese Aufgabe hoch."
> Hinweis: Für `moodle_upload_assignfile` muss der Pfad absolut sein und die Datei lokal existieren (Claude kann die Datei vorher lokal generieren).

**Aktivität ein-/ausblenden oder löschen:**
> "Lies die Module in Abschnitt 2 von Kurs 42 und verstecke die Aktivität 'Entwurf'."

> "Lies die Module in Abschnitt 2 von Kurs 42 und lösche die irrtümlich erstellte Textseite 'Test'."

**Fragen, Kategorien und Quizze:**
> "Welche Fragetypen sind in Kurs 42 verfügbar?"

> "Lege in Kurs 42 eine Fragenkategorie 'LF8 Docker Grundlagen' an."

> "Importiere diese Moodle-XML-Fragen in die Kategorie 17 und erstelle daraus ein Quiz in Abschnitt 3."

Der Fragenimport erwartet vollständiges Moodle-XML mit `<quiz>` als Root-Element.
Für HTML-Inhalte in Fragen sollte CDATA verwendet werden. Jede Frage benötigt
`<name><text>...</text></name>`. Das passt zum Arbeitsablauf des
MoodleQuestionGenerator: erst valides Moodle-XML erzeugen, dann importieren und
die zurückgegebenen `questionids` mit `moodle_add_quiz_questions` einem Quiz
zuordnen.

---

## Bekannte Einschränkungen

**Emojis in Aktivitätstiteln nicht möglich**
Die meisten Moodle-Installationen nutzen `utf8` statt `utf8mb4` als Datenbankzeichensatz.
Emojis im `name`-Feld führen zu einem Datenbankfehler. Im HTML-Inhalt funktionieren
Emojis problemlos als HTML-Entities, z.B. `&#127757;` statt 🌍.

**Sichtbarkeit von Abschnitten**
`update_section` setzt Sichtbarkeit auf Abschnittsebene. Die Sichtbarkeit einzelner
Aktivitäten wird über den `visible`-Parameter der jeweiligen Create/Update-Funktion
gesteuert.

**Voraussetzungen / Abschlussverfolgung**
Damit Voraussetzungen über abgeschlossene Aktivitäten funktionieren, muss in Moodle
die Abschlussverfolgung im Kurs (bzw. systemweit) aktiviert sein.

**Kursformat**
Das Plugin funktioniert mit allen Moodle-Kursformaten (Topics, Weekly usw.).
Die `sectionnum` ist immer 0-basiert (Abschnitt 0 = allgemeiner Bereich).

---

## Projektstruktur

```
moodle-mcp/
├── moodle-mcp.js                  <- Lokaler MCP stdio Server
├── README.md
├── SKILL.md                       <- Claude Skill (Lernsituationen automatisch aufbauen)
└── Plugin/
    └── local_aicoursecreator.zip  <- Moodle Plugin (Webservice-Funktionen)
```

---

## Fehlerbehebung

| Problem | Lösung |
|---|---|
| Hammer-Symbol fehlt in Claude Desktop | Claude Desktop neu starten; JSON-Syntax prüfen |
| `Call to undefined function add_moduleinfo()` | Plugin neu installieren (modlib.php-Fix) |
| `Incorrect string value` Datenbankfehler | Kein Emoji im Titel verwenden |
| `Access denied` | Nutzer dem Dienst als autorisierte Person hinzufügen |
| `Service not found` | Token prüfen; Dienst `AI Course Creator Service` aktiv? |
| Aktivität im falschen Abschnitt | `sectionnum` ist 0-basiert: Abschnitt 1 = `sectionnum: 1` |

---

## Lizenz

MIT
