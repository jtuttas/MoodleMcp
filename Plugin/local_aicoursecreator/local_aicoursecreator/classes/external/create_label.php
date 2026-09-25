<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;

/**
 * Creates a mod_label (Text- und Medienfeld) in a course section.
 */
class create_label extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'   => new external_value(PARAM_INT,  'Course ID'),
            'sectionnum' => new external_value(PARAM_INT,  'Section number (0-based)'),
            'content'    => new external_value(PARAM_RAW,  'HTML content shown directly on course page'),
            'name'       => new external_value(PARAM_TEXT, 'Display name in course management, e.g. "Phase 1 - Informieren"', VALUE_DEFAULT, ''),
            'visible'    => new external_value(PARAM_INT,  'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(int $courseid, int $sectionnum, string $content, string $name = '', int $visible = 1): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'   => $courseid,
            'sectionnum' => $sectionnum,
            'content'    => $content,
            'name'       => $name,
            'visible'    => $visible,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);

        // Name: wenn leer, einen sinnvollen Default aus dem Inhalt ableiten
        // (Moodle zeigt diesen Namen in der Kursverwaltung an)
        $labelname = ($params['name'] !== '') ? $params['name'] : 'label';

        $moduleinfo = new \stdClass();
        $moduleinfo->modulename   = 'label';
        $moduleinfo->module       = $DB->get_field('modules', 'id', ['name' => 'label'], MUST_EXIST);
        $moduleinfo->course       = $params['courseid'];
        $moduleinfo->section      = $params['sectionnum'];
        $moduleinfo->name         = $labelname;
        $moduleinfo->visible      = $params['visible'];
        $moduleinfo->intro        = $params['content'];
        $moduleinfo->introformat  = FORMAT_HTML;

        $moduleinfo = add_moduleinfo($moduleinfo, $course);

        return [
            'cmid'    => (int) $moduleinfo->coursemodule,
            'message' => 'Label "' . $labelname . '" successfully created.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID of the created label'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
