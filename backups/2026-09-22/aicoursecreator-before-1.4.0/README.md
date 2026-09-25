# local_aicoursecreator

Moodle 4.x Plugin – ermöglicht KI-gestützten Kursaufbau via Webservice / MCP.

## Bereitgestellte Webservice-Funktionen

| Funktion | Beschreibung |
|---|---|
| `local_aicoursecreator_get_courses` | Sucht/listet Kurse, auf die der Webservice-Nutzer Zugriff hat |
| `local_aicoursecreator_create_section` | Erstellt einen Kursabschnitt und setzt optional Name/Zusammenfassung |
| `local_aicoursecreator_create_page` | Erstellt eine Textseite (mod_page) in einem Kursabschnitt |
| `local_aicoursecreator_create_assign` | Erstellt eine Aufgabe (mod_assign) in einem Kursabschnitt |
| `local_aicoursecreator_update_section` | Setzt Name und Zusammenfassung eines Abschnitts |
| `local_aicoursecreator_get_sections` | Gibt alle Abschnitte eines Kurses zurück |
| `local_aicoursecreator_set_module_visibility` | Blendet eine Aktivität per cmid ein oder aus |
| `local_aicoursecreator_delete_module` | Löscht eine Aktivität per cmid |
| `local_aicoursecreator_get_question_categories` | Listet Fragenkategorien eines Kurses |
| `local_aicoursecreator_get_question_types` | Listet installierte/verfügbare Fragetypen |
| `local_aicoursecreator_create_question_category` | Erstellt eine Fragenkategorie |
| `local_aicoursecreator_import_questions_xml` | Importiert Moodle-XML-Fragen in eine Kategorie |
| `local_aicoursecreator_create_quiz` | Erstellt eine Quiz-Aktivität |
| `local_aicoursecreator_add_quiz_questions` | Fügt vorhandene Fragen einem Quiz hinzu |
| `local_aicoursecreator_update_quiz` | Bearbeitet Eigenschaften einer Quiz-Aktivität |

---

## Installation

1. **ZIP entpacken** in `[moodle-root]/local/aicoursecreator/`
2. Moodle-Admin-Bereich öffnen → **Upgrade durchführen**
3. Fertig – das Plugin ist installiert

---

## Konfiguration (Webservice + Token)

### 1. Web Services aktivieren
`Website-Administration → Erweiterte Funktionen → Webservices aktivieren` ✅

### 2. REST-Protokoll aktivieren
`Website-Administration → Plugins → Webservices → Protokolle verwalten → REST` ✅

### 3. Token erstellen
`Website-Administration → Server → Webservices → Token verwalten → Token hinzufügen`
- **Nutzer**: Admin oder Lehrer mit Kursbearbeitungsrechten
- **Dienst**: `AI Course Creator Service`
- Token kopieren und sicher aufbewahren

### 4. Mit MCP verbinden (webservice_mcp Plugin)
MCP-Endpoint:
```
https://DEINE-MOODLE-URL/webservice/mcp/server.php?wstoken=DEIN_TOKEN
```

---

## Beispiel-API-Aufruf (REST)

### Textseite erstellen
```
POST https://moodle.example.com/webservice/rest/server.php
wstoken=abc123
wsfunction=local_aicoursecreator_create_page
moodlewsrestformat=json
courseid=5
sectionnum=1
name=Einführung in das Thema
content=<p>Willkommen in diesem Abschnitt...</p>
```

### Aufgabe erstellen
```
POST https://moodle.example.com/webservice/rest/server.php
wstoken=abc123
wsfunction=local_aicoursecreator_create_assign
moodlewsrestformat=json
courseid=5
sectionnum=1
name=Aufgabe 1: Recherche
description=<p>Recherchiere folgende Themen...</p>
duedate=1735689600
maxfiles=3
```

### Abschnitt benennen
```
POST .../server.php
wsfunction=local_aicoursecreator_update_section
courseid=5
sectionnum=1
name=Lerneinheit 1: Grundlagen
summary=<p>In diesem Abschnitt lernst du...</p>
```

### Abschnitt erstellen
```
POST .../server.php
wsfunction=local_aicoursecreator_create_section
courseid=5
sectionnum=3
name=Lerneinheit 3: Sensorik
summary=<p>Neuer Kursabschnitt...</p>
visible=1
```

### Moodle-XML-Fragen importieren und Quiz erstellen
```
POST .../server.php
wsfunction=local_aicoursecreator_create_question_category
courseid=5
name=LF8 Docker Grundlagen
```

```
POST .../server.php
wsfunction=local_aicoursecreator_import_questions_xml
courseid=5
categoryid=17
xml=<?xml version="1.0" encoding="UTF-8"?><quiz>...</quiz>
```

```
POST .../server.php
wsfunction=local_aicoursecreator_create_quiz
courseid=5
sectionnum=3
name=Quiz: Docker Grundlagen
```

```
POST .../server.php
wsfunction=local_aicoursecreator_add_quiz_questions
cmid=123
questionids[0]=456
questionids[1]=457
maxmark=1
```

---

## Parameter-Referenz

### create_page
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| courseid | int | ✅ | Kurs-ID |
| sectionnum | int | ✅ | Abschnittsnummer (0-basiert) |
| name | string | ✅ | Titel der Textseite |
| content | string | ✅ | HTML-Inhalt |
| visible | int | – | 1=sichtbar (Standard), 0=versteckt |

### create_assign
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| courseid | int | ✅ | Kurs-ID |
| sectionnum | int | ✅ | Abschnittsnummer (0-basiert) |
| name | string | ✅ | Titel der Aufgabe |
| description | string | – | HTML-Beschreibung |
| duedate | int | – | Abgabedatum als Unix-Timestamp (0 = kein Datum) |
| allowsubmissionsfromdate | int | – | Freischaltdatum (0 = sofort) |
| maxfiles | int | – | Max. Dateiuploads (Standard: 1, 0 = kein Upload) |
| submissiondrafts | int | – | 1 = Schüler müssen Submit klicken |
| visible | int | – | 1=sichtbar (Standard) |

### update_section
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| courseid | int | ✅ | Kurs-ID |
| sectionnum | int | ✅ | Abschnittsnummer |
| name | string | – | Abschnittsname |
| summary | string | – | HTML-Zusammenfassung |
| visible | int | – | 1=sichtbar (Standard) |

### create_section
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| courseid | int | ✅ | Kurs-ID |
| sectionnum | int | ✅ | Abschnittsnummer |
| name | string | – | Abschnittsname |
| summary | string | – | HTML-Zusammenfassung |
| visible | int | – | 1=sichtbar (Standard), 0=versteckt |

### get_courses
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| search | string | – | Suchtext für Kursname, Kurzname oder ID-Nummer |
| limit | int | – | Maximale Anzahl Ergebnisse (Standard: 50, maximal 200) |

### set_module_visibility / delete_module
| Parameter | Typ | Pflicht | Beschreibung |
|---|---|---|---|
| cmid | int | ✅ | Course Module ID aus `get_modules` |
| visible | int | nur set_module_visibility | 1=sichtbar, 0=versteckt |

### question categories / Moodle XML / quiz
| Funktion | Wichtige Parameter | Beschreibung |
|---|---|---|
| get_question_categories | courseid | Kategorien der Kurs-Fragensammlung lesen |
| get_question_types | courseid | Verfügbare Fragetypen für Moodle-XML prüfen |
| create_question_category | courseid, name, info, parentid | Kategorie unter der Kurs-Top-Kategorie oder einer Parent-Kategorie anlegen |
| import_questions_xml | courseid, categoryid, xml, filename | Vollständiges Moodle-XML in eine Kategorie importieren |
| create_quiz | courseid, sectionnum, name, intro, grade | Quiz-Aktivität anlegen |
| add_quiz_questions | cmid, questionids[], maxmark | Vorhandene Fragen einem Quiz hinzufügen |
| update_quiz | cmid, name, intro, timelimit, attempts, grademethod, visible | Bestehendes Quiz bearbeiten |

Der XML-Import folgt dem MoodleQuestionGenerator-Workflow: Das Tool erwartet
valides Moodle-XML mit `<quiz>` als Root-Element. Jede Frage muss
`<name><text>...</text></name>` enthalten; HTML-Inhalte sollten in CDATA-Blöcken
stehen. Unterstützte Fragetypen hängen von Moodle und den installierten
Fragetyp-Plugins ab, z.B. `multichoice`, `truefalse`, `shortanswer`, `cloze`,
`ddwtos` oder `coderunner`.

---

## Kompatibilität
- Moodle 4.0 – 4.5+
- PHP 7.4 / 8.0 / 8.1 / 8.2
