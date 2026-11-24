<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/filelib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_module;

/**
 * Laedt eine Datei (als Base64) in den "Zusaetzliche Dateien"-Bereich
 * einer mod_assign-Aktivitaet hoch.
 */
class upload_assignfile extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'     => new external_value(PARAM_INT,  'Course module ID der Aufgabe'),
            'filename' => new external_value(PARAM_FILE, 'Dateiname inkl. Erweiterung'),
            'content'  => new external_value(PARAM_RAW,  'Base64-kodierter Dateiinhalt'),
            'mimetype' => new external_value(PARAM_RAW,  'MIME-Type', VALUE_DEFAULT, 'application/octet-stream'),
        ]);
    }

    public static function execute(int $cmid, string $filename, string $content, string $mimetype = 'application/octet-stream'): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'     => $cmid,
            'filename' => $filename,
            'content'  => $content,
            'mimetype' => $mimetype,
        ]);

        // Kontext und Rechte pruefen
        $cm = get_coursemodule_from_id('assign', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Base64 dekodieren
        $filedata = base64_decode($params['content'], true);
        if ($filedata === false) {
            throw new \invalid_parameter_exception('Ungueltige Base64-Kodierung.');
        }

        $fs = get_file_storage();

        // Alle vorhandenen Dateien gleichen Namens im filearea loeschen
        $existing_files = $fs->get_area_files(
            $context->id,
            'mod_assign',
            'introattachment',
            0,
            'filename',
            false
        );
        foreach ($existing_files as $existing) {
            if ($existing->get_filename() === $params['filename']) {
                $existing->delete();
            }
        }

        // Kurze Pause damit DB-Transaktion sicher abgeschlossen ist
        usleep(100000); // 100ms

        // Neue Datei anlegen
        $fileinfo = [
            'contextid' => $context->id,
            'component' => 'mod_assign',
            'filearea'  => 'introattachment',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $params['filename'],
            'mimetype'  => $params['mimetype'],
            'userid'    => $USER->id,
            'source'    => $params['filename'],
            'author'    => fullname($USER),
            'license'   => 'allrightsreserved',
        ];

        $file = $fs->create_file_from_string($fileinfo, $filedata);
        $fileid = (int) $file->get_id();

        // introattachments aktivieren falls noetig
        $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST);
        if (empty($assign->introattachments)) {
            $DB->set_field('assign', 'introattachments', 10, ['id' => $cm->instance]);
        }
        if (!isset($assign->introattachmentsonsubmission) || $assign->introattachmentsonsubmission != 0) {
            $DB->set_field('assign', 'introattachmentsonsubmission', 0, ['id' => $cm->instance]);
        }

        // Cache neu aufbauen – Fehler hier abfangen da Upload bereits
        // erfolgreich war und rebuild_course_cache nur Optimierung ist
        try {
            rebuild_course_cache($cm->course, true);
        } catch (\dml_write_exception $e) {
            // Cache-Rebuild fehlgeschlagen aber Datei ist korrekt gespeichert.
            // Fehler ignorieren – Moodle baut den Cache beim naechsten
            // Seitenaufruf automatisch neu auf.
        } catch (\Exception $e) {
            // Alle anderen Cache-Fehler ebenfalls ignorieren
        }

        return [
            'fileid'  => $fileid,
            'message' => 'Datei "' . $params['filename'] . '" erfolgreich hochgeladen (cmid: ' . $cm->id . ').',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'fileid'  => new external_value(PARAM_INT,  'ID der hochgeladenen Datei in mdl_files'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
