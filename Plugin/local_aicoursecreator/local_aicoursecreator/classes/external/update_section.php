<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;

/**
 * Updates section name and summary.
 */
class update_section extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'   => new external_value(PARAM_INT,  'Course ID'),
            'sectionnum' => new external_value(PARAM_INT,  'Section number (0-based)'),
            'name'       => new external_value(PARAM_TEXT, 'Section name', VALUE_DEFAULT, ''),
            'summary'    => new external_value(PARAM_RAW,  'Section summary (HTML)', VALUE_DEFAULT, ''),
            'visible'    => new external_value(PARAM_INT,  'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(int $courseid, int $sectionnum, string $name = '', string $summary = '', int $visible = 1): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'   => $courseid,
            'sectionnum' => $sectionnum,
            'name'       => $name,
            'summary'    => $summary,
            'visible'    => $visible,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:update', $context);

        $section = $DB->get_record('course_sections',
            ['course' => $params['courseid'], 'section' => $params['sectionnum']],
            '*', MUST_EXIST);

        $data = new \stdClass();
        $data->id             = $section->id;
        $data->name           = $params['name'];
        $data->summary        = $params['summary'];
        $data->summaryformat  = FORMAT_HTML;
        $data->visible        = $params['visible'];

        $DB->update_record('course_sections', $data);
        rebuild_course_cache($params['courseid'], true);

        return [
            'sectionid' => (int) $section->id,
            'message'   => 'Section ' . $params['sectionnum'] . ' updated successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'sectionid' => new external_value(PARAM_INT,  'Section DB ID'),
            'message'   => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
