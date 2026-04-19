# moodle-mcp

KI-gestützter Moodle-Kursaufbau via Claude Desktop und MCP.

Claude Desktop spricht direkt mit der Moodle REST API und kann Kursabschnitte,
Textseiten, Labels, Aufgaben und externe Links anlegen und bearbeiten.

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
| `moodle_get_sections` | Alle Abschnitte eines Kurses lesen |
| `moodle_get_modules` | Alle Aktivitäten eines Abschnitts mit cmid lesen |
| `moodle_update_section` | Abschnittsname und Beschreibung setzen |
| `moodle_create_label` | Text- und Medienfeld anlegen (Phasen-Header) |
| `moodle_update_label` | Text- und Medienfeld bearbeiten |
| `moodle_create_page` | Textseite anlegen |
| `moodle_update_page` | Textseite bearbeiten |
| `moodle_create_url` | Externen Link anlegen |
| `moodle_update_url` | Externen Link bearbeiten |
| `moodle_create_assign` | Aufgabe anlegen |
| `moodle_update_assign` | Aufgabe bearbeiten |

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

**Einen Abschnitt benennen:**
> "Benenne Abschnitt 1 in Kurs 42 als 'LS 7.2 – ESP32 Webserver'"

**Komplette Lernsituation aufbauen:**
> "Baue in Kurs 42, Abschnitt 2 eine Lernsituation zum Thema ESP32 Webserver auf.
> Firma ist TechSolutions GmbH. Erstelle alle 5 Handlungsphasen mit passenden
> Aktivitäten."

**Bestehende Seite bearbeiten:**
> "Lies die Module in Abschnitt 1 von Kurs 42 und ändere den Inhalt der
> Textseite 'Informationsblatt' auf einen aktualisierten Text."

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

**Kursformat**
Das Plugin funktioniert mit allen Moodle-Kursformaten (Topics, Weekly usw.).
Die `sectionnum` ist immer 0-basiert (Abschnitt 0 = allgemeiner Bereich).

---

## Projektstruktur

```
moodle-mcp/
├── moodle-mcp.js                  <- Lokaler MCP stdio Server
└── local_aicoursecreator/         <- Moodle Plugin
    ├── version.php
    ├── lib.php
    ├── db/
    │   └── services.php           <- Webservice-Definitionen
    ├── lang/en/
    │   └── local_aicoursecreator.php
    └── classes/external/
        ├── get_sections.php
        ├── get_modules.php
        ├── update_section.php
        ├── create_label.php
        ├── update_label.php
        ├── create_page.php
        ├── update_page.php
        ├── create_url.php
        ├── update_url.php
        ├── create_assign.php
        └── update_assign.php
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
