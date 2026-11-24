<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;
use core_question\local\bank\question_edit_contexts;

class import_questions_xml extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'   => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Target question category ID'),
            'xml'        => new external_value(PARAM_RAW, 'Complete Moodle XML with <quiz> root'),
            'filename'   => new external_value(PARAM_FILE, 'Logical import filename', VALUE_DEFAULT, 'questions.xml'),
        ]);
    }

    public static function execute(int $courseid, int $categoryid, string $xml, string $filename = 'questions.xml'): array {
        global $DB, $CFG;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'   => $courseid,
            'categoryid' => $categoryid,
            'xml'        => $xml,
            'filename'   => $filename,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/question:add', $context);

        $category = $DB->get_record('question_categories', ['id' => $params['categoryid']], '*', MUST_EXIST);
        if ((int) $category->contextid !== (int) $context->id) {
            throw new \invalid_parameter_exception('Question category belongs to another course context.');
        }

        self::validate_moodle_xml($params['xml']);

        $before = self::get_question_ids_for_category($params['categoryid']);

        $tmpdir = make_request_directory();
        $tmpfile = $tmpdir . '/questions.xml';
        file_put_contents($tmpfile, $params['xml']);

        $contexts = new question_edit_contexts($context);
        $qformat = new \qformat_xml();
        $qformat->setContexts($contexts->having_one_edit_tab_cap('import'));
        $qformat->setCourse($DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST));
        $qformat->setCategory($category);
        $qformat->setFilename($tmpfile);
        $qformat->setRealfilename($params['filename']);
        $qformat->setMatchgrades('error');
        $qformat->setCatfromfile(false);
        $qformat->setContextfromfile(false);
        $qformat->setStoponerror(true);

        ob_start();
        try {
            $imported = $qformat->importprocess();
        } finally {
            ob_end_clean();
        }

        if (!$imported) {
            throw new \moodle_exception('questionimportfailed', 'question');
        }

        $after = self::get_question_ids_for_category($params['categoryid']);
        $newids = array_values(array_diff($after, $before));

        return [
            'categoryid' => (int) $params['categoryid'],
            'imported'   => count($newids),
            'questionids' => $newids,
            'message'    => count($newids) . ' question(s) imported successfully.',
        ];
    }

    private static function validate_moodle_xml(string $xml): void {
        $trimmed = trim($xml);
        if ($trimmed === '' || strpos($trimmed, '<quiz') === false) {
            throw new \invalid_parameter_exception('Moodle XML must contain a <quiz> root element.');
        }

        $previous = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($trimmed);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$doc || $doc->getName() !== 'quiz') {
            throw new \invalid_parameter_exception('Moodle XML root element must be <quiz>.');
        }

        foreach ($doc->question as $question) {
            if (!isset($question->name->text) || trim((string) $question->name->text) === '') {
                throw new \invalid_parameter_exception('Every Moodle XML question must contain <name><text>...</text></name>.');
            }
        }
    }

    private static function get_question_ids_for_category(int $categoryid): array {
        global $DB;

        if ($DB->get_manager()->table_exists('question_bank_entries')) {
            $sql = "SELECT q.id
                      FROM {question} q
                      JOIN {question_versions} qv ON qv.questionid = q.id
                      JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                     WHERE qbe.questioncategoryid = :categoryid";
            return array_map('intval', array_keys($DB->get_records_sql($sql, ['categoryid' => $categoryid])));
        }

        return array_map('intval', array_keys($DB->get_records('question', ['category' => $categoryid], '', 'id')));
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'categoryid' => new external_value(PARAM_INT, 'Target question category ID'),
            'imported'   => new external_value(PARAM_INT, 'Number of imported questions'),
            'questionids' => new external_multiple_structure(new external_value(PARAM_INT, 'Imported question ID')),
            'message'    => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
