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

class set_module_visibility extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'    => new external_value(PARAM_INT, 'Course module ID'),
            'visible' => new external_value(PARAM_INT, 'Visible (1) or hidden (0)'),
        ]);
    }

    public static function execute(int $cmid, int $visible): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'    => $cmid,
            'visible' => $visible,
        ]);

        $cm = get_coursemodule_from_id('', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_course::instance($cm->course);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        set_coursemodule_visible($params['cmid'], $params['visible'] ? 1 : 0);
        rebuild_course_cache($cm->course, true);

        return [
            'cmid'    => (int) $params['cmid'],
            'visible' => $params['visible'] ? 1 : 0,
            'message' => 'Module visibility updated successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'visible' => new external_value(PARAM_INT,  'Visible (1) or hidden (0)'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
