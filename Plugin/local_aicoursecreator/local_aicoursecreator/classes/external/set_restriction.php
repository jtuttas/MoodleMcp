<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_module;

/**
 * Setzt Zugangsvoraussetzungen (Restrict Access) fuer eine Aktivitaet.
 *
 * Eine Aktivitaet kann mehrere Voraussetzungen haben:
 *   - Abschluss einer anderen Aktivitaet (type=completion)
 *   - Datum (type=date) – noch nicht implementiert
 *
 * Das availability-JSON-Format von Moodle:
 * {
 *   "op": "&",           // & = alle Bedingungen, | = eine reicht
 *   "c": [
 *     {"type":"completion","cm":CMID,"e":1}   // e=1: muss abgeschlossen sein
 *   ],
 *   "showc": [true]      // true = gesperrte Aktivitaet anzeigen (ausgegraut)
 * }
 */
class set_restriction extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'          => new external_value(PARAM_INT, 'Course module ID der Aktivitaet die gesperrt werden soll'),
            'require_cmids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'cmid einer Aktivitaet die abgeschlossen sein muss'),
                'Liste der cmids die abgeschlossen sein muessen (AND-Verknuepfung)'
            ),
            'show_locked'   => new external_value(PARAM_INT,
                '1 = gesperrte Aktivitaet ausgegraut anzeigen, 0 = komplett verstecken',
                VALUE_DEFAULT, 1),
            'operator'      => new external_value(PARAM_ALPHA,
                'AND = alle Bedingungen, OR = eine reicht',
                VALUE_DEFAULT, 'AND'),
        ]);
    }

    public static function execute(int $cmid, array $require_cmids, int $show_locked = 1, string $operator = 'AND'): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'          => $cmid,
            'require_cmids' => $require_cmids,
            'show_locked'   => $show_locked,
            'operator'      => $operator,
        ]);

        // Kontext und Rechte
        $cm = get_coursemodule_from_id(null, $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        if (empty($params['require_cmids'])) {
            // Voraussetzungen loeschen
            $DB->set_field('course_modules', 'availability', null, ['id' => $cm->id]);
            rebuild_course_cache($cm->course, true);
            return [
                'cmid'    => (int) $cm->id,
                'message' => 'Voraussetzungen fuer cmid ' . $cm->id . ' entfernt.',
            ];
        }

        // Moodle availability JSON aufbauen
        $op = ($params['operator'] === 'OR') ? '|' : '&';

        $conditions = [];
        $showconds  = [];
        foreach ($params['require_cmids'] as $req_cmid) {
            $conditions[] = [
                'type' => 'completion',
                'cm'   => (int) $req_cmid,
                'e'    => 1,  // e=1: muss abgeschlossen sein
            ];
            $showconds[] = (bool) $params['show_locked'];
        }

        $availability = json_encode([
            'op'    => $op,
            'c'     => $conditions,
            'showc' => $showconds,
        ]);

        $DB->set_field('course_modules', 'availability', $availability, ['id' => $cm->id]);
        rebuild_course_cache($cm->course, true);

        return [
            'cmid'    => (int) $cm->id,
            'message' => 'Voraussetzungen fuer cmid ' . $cm->id . ' gesetzt: ' . implode(', ', $params['require_cmids']),
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
