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
use context_course;

/**
 * Returns all sections of a course.
 */
class get_sections extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    public static function execute(int $courseid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $sections = $DB->get_records('course_sections',
            ['course' => $params['courseid']],
            'section ASC',
            'id, section, name, summary, visible'
        );

        $result = [];
        foreach ($sections as $s) {
            $result[] = [
                'id'         => (int) $s->id,
                'sectionnum' => (int) $s->section,
                'name'       => $s->name ?? '',
                'summary'    => $s->summary ?? '',
                'visible'    => (int) $s->visible,
            ];
        }

        return $result;
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id'         => new external_value(PARAM_INT,  'Section DB ID'),
                'sectionnum' => new external_value(PARAM_INT,  'Section number (0-based)'),
                'name'       => new external_value(PARAM_TEXT, 'Section name'),
                'summary'    => new external_value(PARAM_RAW,  'Section summary HTML'),
                'visible'    => new external_value(PARAM_INT,  'Visible flag'),
            ])
        );
    }
}
