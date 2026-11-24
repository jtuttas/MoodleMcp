<?php
namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class get_assign_content extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID of the Assignment activity'),
        ]);
    }

    public static function execute(int $cmid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        $cm = get_coursemodule_from_id('assign', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($params['cmid']);
        self::validate_context($context);
        require_capability('mod/assign:view', $context);

        $assign = $DB->get_record(
            'assign',
            ['id' => $cm->instance],
            'id, course, name, intro, introformat, duedate',
            MUST_EXIST
        );
        $sectionnum = $DB->get_field(
            'course_sections',
            'section',
            ['id' => $cm->section],
            MUST_EXIST
        );

        return [
            'cmid' => (int) $cm->id,
            'courseid' => (int) $cm->course,
            'sectionnum' => (int) $sectionnum,
            'instanceid' => (int) $assign->id,
            'name' => $assign->name,
            'visible' => (int) $cm->visible,
            'descriptionformat' => (int) $assign->introformat,
            'description' => $assign->intro,
            'duedate' => (int) $assign->duedate,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionnum' => new external_value(PARAM_INT, 'Section number'),
            'instanceid' => new external_value(PARAM_INT, 'Assignment instance ID'),
            'name' => new external_value(PARAM_TEXT, 'Assignment name'),
            'visible' => new external_value(PARAM_INT, 'Visible (1) or hidden (0)'),
            'descriptionformat' => new external_value(PARAM_INT, 'Moodle text format'),
            'description' => new external_value(PARAM_RAW, 'Raw stored Assignment HTML description'),
            'duedate' => new external_value(PARAM_INT, 'Assignment due date as Unix timestamp'),
        ]);
    }
}
