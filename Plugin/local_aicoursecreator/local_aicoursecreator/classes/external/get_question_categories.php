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

class get_question_categories extends external_api {

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
        require_capability('moodle/question:viewall', $context);

        $records = $DB->get_records('question_categories', ['contextid' => $context->id], 'sortorder ASC, name ASC',
            'id, name, parent, contextid, info, sortorder, idnumber');

        $result = [];
        foreach ($records as $category) {
            $result[] = [
                'id'        => (int) $category->id,
                'name'      => $category->name,
                'parent'    => (int) $category->parent,
                'contextid' => (int) $category->contextid,
                'info'      => $category->info,
                'sortorder' => (int) $category->sortorder,
                'idnumber'  => $category->idnumber ?? '',
            ];
        }

        return $result;
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id'        => new external_value(PARAM_INT,  'Question category ID'),
                'name'      => new external_value(PARAM_TEXT, 'Category name'),
                'parent'    => new external_value(PARAM_INT,  'Parent category ID'),
                'contextid' => new external_value(PARAM_INT,  'Context ID'),
                'info'      => new external_value(PARAM_RAW,  'Category info'),
                'sortorder' => new external_value(PARAM_INT,  'Sort order'),
                'idnumber'  => new external_value(PARAM_RAW,  'Category idnumber'),
            ])
        );
    }
}
