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
 * Creates a mod_url (Link/URL) activity in a course section.
 */
class create_url extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'    => new external_value(PARAM_INT,  'Course ID'),
            'sectionnum'  => new external_value(PARAM_INT,  'Section number (0-based)'),
            'name'        => new external_value(PARAM_TEXT, 'Display name of the link'),
            'externalurl' => new external_value(PARAM_URL,  'Full external URL including https://'),
            'intro'       => new external_value(PARAM_RAW,  'Short description (optional)', VALUE_DEFAULT, ''),
            'visible'     => new external_value(PARAM_INT,  'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(int $courseid, int $sectionnum, string $name, string $externalurl, string $intro = '', int $visible = 1): array {
        global $DB, $CFG;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'    => $courseid,
            'sectionnum'  => $sectionnum,
            'name'        => $name,
            'externalurl' => $externalurl,
            'intro'       => $intro,
            'visible'     => $visible,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);

        $moduleinfo = new \stdClass();
        $moduleinfo->modulename   = 'url';
        $moduleinfo->module       = $DB->get_field('modules', 'id', ['name' => 'url'], MUST_EXIST);
        $moduleinfo->course       = $params['courseid'];
        $moduleinfo->section      = $params['sectionnum'];
        $moduleinfo->name         = $params['name'];
        $moduleinfo->visible      = $params['visible'];
        $moduleinfo->intro        = $params['intro'];
        $moduleinfo->introformat  = FORMAT_HTML;
        $moduleinfo->externalurl  = $params['externalurl'];
        $moduleinfo->display      = 0; // In neuem Tab öffnen
        $moduleinfo->displayoptions = serialize(['printintro' => 0]);

        $moduleinfo = add_moduleinfo($moduleinfo, $course);

        return [
            'cmid'    => (int) $moduleinfo->coursemodule,
            'message' => 'URL "' . $params['name'] . '" successfully created.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID of the created URL'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
