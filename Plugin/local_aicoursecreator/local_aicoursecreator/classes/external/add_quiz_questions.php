<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use context_course;

class add_quiz_questions extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'        => new external_value(PARAM_INT, 'Course module ID of the quiz'),
            'questionids' => new external_multiple_structure(new external_value(PARAM_INT, 'Question ID')),
            'maxmark'     => new external_value(PARAM_FLOAT, 'Mark per question', VALUE_DEFAULT, 1.0),
        ]);
    }

    public static function execute(int $cmid, array $questionids, float $maxmark = 1.0): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'        => $cmid,
            'questionids' => $questionids,
            'maxmark'     => $maxmark,
        ]);

        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_course::instance($cm->course);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);

        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);
        $added = [];

        foreach ($params['questionids'] as $questionid) {
            $DB->get_record('question', ['id' => $questionid], 'id', MUST_EXIST);
            \quiz_add_quiz_question((int) $questionid, $quiz, 0, $params['maxmark']);
            $added[] = (int) $questionid;
        }

        \quiz_update_sumgrades($quiz);
        \quiz_update_all_final_grades($quiz);

        return [
            'cmid'        => (int) $params['cmid'],
            'quizid'      => (int) $quiz->id,
            'added'       => count($added),
            'questionids' => $added,
            'message'     => count($added) . ' question(s) added to quiz successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'        => new external_value(PARAM_INT, 'Course module ID'),
            'quizid'      => new external_value(PARAM_INT, 'Quiz instance ID'),
            'added'       => new external_value(PARAM_INT, 'Number of added questions'),
            'questionids' => new external_multiple_structure(new external_value(PARAM_INT, 'Added question ID')),
            'message'     => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
