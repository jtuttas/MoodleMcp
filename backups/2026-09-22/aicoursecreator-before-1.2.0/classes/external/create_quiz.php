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

class create_quiz extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'           => new external_value(PARAM_INT, 'Course ID'),
            'sectionnum'         => new external_value(PARAM_INT, 'Section number (0-based)'),
            'name'               => new external_value(PARAM_TEXT, 'Quiz name'),
            'intro'              => new external_value(PARAM_RAW, 'Quiz intro HTML', VALUE_DEFAULT, ''),
            'grade'              => new external_value(PARAM_FLOAT, 'Maximum grade', VALUE_DEFAULT, 10.0),
            'questionsperpage'   => new external_value(PARAM_INT, 'Questions per page', VALUE_DEFAULT, 1),
            'shuffleanswers'     => new external_value(PARAM_INT, 'Shuffle answers (1/0)', VALUE_DEFAULT, 1),
            'preferredbehaviour' => new external_value(PARAM_TEXT, 'Question behaviour', VALUE_DEFAULT, 'deferredfeedback'),
            'visible'            => new external_value(PARAM_INT, 'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(
        int $courseid,
        int $sectionnum,
        string $name,
        string $intro = '',
        float $grade = 10.0,
        int $questionsperpage = 1,
        int $shuffleanswers = 1,
        string $preferredbehaviour = 'deferredfeedback',
        int $visible = 1
    ): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'           => $courseid,
            'sectionnum'         => $sectionnum,
            'name'               => $name,
            'intro'              => $intro,
            'grade'              => $grade,
            'questionsperpage'   => $questionsperpage,
            'shuffleanswers'     => $shuffleanswers,
            'preferredbehaviour' => $preferredbehaviour,
            'visible'            => $visible,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $now = time();

        // 1. Insert into mdl_quiz directly (avoids mod_form/add_moduleinfo complexity in Moodle 4.5).
        $quiz = new \stdClass();
        $quiz->course              = $params['courseid'];
        $quiz->name                = $params['name'];
        $quiz->intro               = $params['intro'];
        $quiz->introformat         = FORMAT_HTML;
        $quiz->timeopen            = 0;
        $quiz->timeclose           = 0;
        $quiz->timelimit           = 0;
        $quiz->overduehandling     = 'autosubmit';
        $quiz->graceperiod         = 0;
        $quiz->preferredbehaviour  = $params['preferredbehaviour'];
        $quiz->canredoquestions    = 0;
        $quiz->attempts            = 0;
        $quiz->attemptonlast       = 0;
        $quiz->grademethod         = 1; // QUIZ_GRADEHIGHEST
        $quiz->decimalpoints       = 2;
        $quiz->questiondecimalpoints = -1;
        $quiz->reviewattempt       = 69888;  // show after close
        $quiz->reviewcorrectness   = 4352;
        $quiz->reviewmarks         = 69888;
        $quiz->reviewspecificfeedback = 69888;
        $quiz->reviewgeneralfeedback  = 69888;
        $quiz->reviewrightanswer      = 4352;
        $quiz->reviewoverallfeedback  = 4352;
        $quiz->questionsperpage    = $params['questionsperpage'];
        $quiz->navmethod           = 'free';
        $quiz->shuffleanswers      = $params['shuffleanswers'] ? 1 : 0;
        $quiz->sumgrades           = 0;
        $quiz->grade               = $params['grade'];
        $quiz->timecreated         = $now;
        $quiz->timemodified        = $now;
        $quiz->password            = '';
        $quiz->subnet              = '';
        $quiz->browsersecurity     = '-';
        $quiz->delay1              = 0;
        $quiz->delay2              = 0;
        $quiz->showuserpicture     = 0;
        $quiz->showblocks          = 0;
        $quiz->completionattemptsexhausted = 0;
        $quiz->allowofflineattempts = 0;

        $quizid = $DB->insert_record('quiz', $quiz);

        // Required: quiz_add_instance() always creates the first quiz section (question
        // heading/grouping row). Without it, mod_quiz\structure cannot render the quiz's
        // questions, even though quiz_slots/question_references are correctly populated.
        $DB->insert_record('quiz_sections', [
            'quizid'           => $quizid,
            'firstslot'        => 1,
            'heading'          => '',
            'shufflequestions' => 0,
        ]);

        // Required: at least one feedback row (grade >= 0, grade <= max).
        $feedback = new \stdClass();
        $feedback->quizid          = $quizid;
        $feedback->feedbacktext    = '';
        $feedback->feedbacktextformat = FORMAT_HTML;
        $feedback->mingrade        = 0;
        $feedback->maxgrade        = 0;
        $DB->insert_record('quiz_feedback', $feedback);

        // 2. Create course_module entry.
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST);
        $cm = new \stdClass();
        $cm->course     = $params['courseid'];
        $cm->module     = $moduleid;
        $cm->instance   = $quizid;
        $cm->section    = 0; // will be updated by course_add_cm_to_section
        $cm->visible    = $params['visible'];
        $cm->added      = $now;
        $cm->score      = 0;
        $cm->indent     = 0;
        $cm->groupmode  = 0;
        $cm->groupingid = 0;
        $cm->completion = 0;
        $cmid = $DB->insert_record('course_modules', $cm);

        // 3. Add to the correct section.
        course_add_cm_to_section($params['courseid'], $cmid, $params['sectionnum']);

        // 4. Create module context (required for capability checks on this module).
        \context_module::instance($cmid);

        // 5. Rebuild course cache.
        rebuild_course_cache($params['courseid'], true);

        return [
            'cmid'    => (int) $cmid,
            'quizid'  => (int) $quizid,
            'message' => 'Quiz "' . $params['name'] . '" successfully created.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT, 'Course module ID of the created quiz'),
            'quizid'  => new external_value(PARAM_INT, 'Quiz instance ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
