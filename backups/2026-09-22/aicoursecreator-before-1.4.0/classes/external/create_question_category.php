<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;

class create_question_category extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT,  'Course ID'),
            'name'     => new external_value(PARAM_TEXT, 'Question category name'),
            'info'     => new external_value(PARAM_RAW,  'Category info HTML', VALUE_DEFAULT, ''),
            'parentid' => new external_value(PARAM_INT,  'Parent question category ID, 0 = course top category', VALUE_DEFAULT, 0),
            'idnumber' => new external_value(PARAM_RAW,  'Optional category idnumber', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute(int $courseid, string $name, string $info = '', int $parentid = 0, string $idnumber = ''): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'name'     => $name,
            'info'     => $info,
            'parentid' => $parentid,
            'idnumber' => $idnumber,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/question:managecategory', $context);

        $parentid = $params['parentid'];
        if ($parentid > 0) {
            $parent = $DB->get_record('question_categories', ['id' => $parentid], '*', MUST_EXIST);
            if ((int) $parent->contextid !== (int) $context->id) {
                throw new \invalid_parameter_exception('Parent category belongs to another context.');
            }
        } else {
            $top = $DB->get_record('question_categories', ['contextid' => $context->id, 'parent' => 0], '*', IGNORE_MULTIPLE);
            if (!$top) {
                $topdata = new \stdClass();
                $topdata->name = 'top';
                $topdata->contextid = $context->id;
                $topdata->info = '';
                $topdata->infoformat = FORMAT_HTML;
                $topdata->stamp = make_unique_id_code();
                $topdata->parent = 0;
                $topdata->sortorder = 0;
                $topdata->idnumber = null;
                $topdata->id = $DB->insert_record('question_categories', $topdata);
                $top = $topdata;
            }
            $parentid = (int) $top->id;
        }

        $sortorder = (int) $DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) + 1 FROM {question_categories} WHERE contextid = :contextid',
            ['contextid' => $context->id]
        );

        $category = new \stdClass();
        $category->name = $params['name'];
        $category->contextid = $context->id;
        $category->info = $params['info'];
        $category->infoformat = FORMAT_HTML;
        $category->stamp = make_unique_id_code();
        $category->parent = $parentid;
        $category->sortorder = $sortorder;
        $category->idnumber = ($params['idnumber'] !== '') ? $params['idnumber'] : null;
        $category->id = $DB->insert_record('question_categories', $category);

        return [
            'categoryid' => (int) $category->id,
            'parentid'   => (int) $parentid,
            'message'    => 'Question category "' . $params['name'] . '" created successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'categoryid' => new external_value(PARAM_INT,  'Created question category ID'),
            'parentid'   => new external_value(PARAM_INT,  'Parent question category ID'),
            'message'    => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
