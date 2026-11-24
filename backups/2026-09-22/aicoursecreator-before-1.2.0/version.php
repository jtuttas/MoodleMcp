<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_aicoursecreator';
$plugin->version   = 2025092102;  // Format: YYYYMMDDNN – NN bei mehreren Releases pro Tag hochzählen
$plugin->requires  = 2022041900;  // Moodle 4.0+
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.2.0';

// Changelog:
// 1.2.0 (2025092102) – Neues Tool: move_module (Aktivitaet innerhalb eines Abschnitts
//   verschieben oder in einen anderen Abschnitt verschieben, ueber Moodles moveto_module()).
// 1.1.7 (2025092101) – Bugfix: create_quiz legte keinen quiz_sections-Eintrag an, dadurch
//   zeigten neu erstellte Quizzes trotz korrekt verknuepfter Fragen keine Fragen an.
// 1.1.6 (2025053007) – mod/quiz/locallib.php fuer quiz_add_quiz_question eingebunden
// 1.1.5 (2025053006) – Globale Quiz-Funktionen in add_quiz_questions korrekt aufgerufen
// 1.1.4 (2025053005) – HTML-Ausgabe des Moodle-XML-Imports fuer REST/MCP unterdrueckt
// 1.1.3 (2025053004) – Moodle-4.x-Namespace fuer question_edit_contexts im XML-Import korrigiert
// 1.1.2 (2025053003) – update_quiz Berechtigungsprüfung auf Modulkontext/Quiz-Manage korrigiert
// 1.1.1 (2025053002) – update_quiz in Service-Funktionsliste und Plugin-ZIP ergänzt
// 1.0.12 (2025041912) – Neues Tool: get_question_types
// 1.0.11 (2025041911) – Neue Tools: question categories, Moodle-XML question import, quiz creation, add quiz questions
// 1.0.10 (2025041910) – Neue Tools: get_courses, create_section, set_module_visibility, delete_module
// 1.0.2 (2025041902) – Bugfix: course_modules hat kein timemodified-Feld
// 1.0.1 (2025041901) – Bugfixes:
//   - modlib.php require_once ergänzt (add_moduleinfo war nicht gefunden)
//   - markingworkflow + markingallocation in create_assign ergänzt
//   - update_* Funktionen: !== '' statt if() + timemodified immer setzen
//   - create_url + create_label als neue Funktionen
// 1.0.0 (2025041800) – Erstveröffentlichung
