<?php
namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;
use context_module;

class update_quiz extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'               => new external_value(PARAM_INT,   'Course module ID of the quiz'),
            'name'               => new external_value(PARAM_TEXT,  'New quiz title (empty = no change)', VALUE_DEFAULT, ''),
            'intro'              => new external_value(PARAM_RAW,   'New HTML intro (empty = no change)', VALUE_DEFAULT, ''),
            'timelimit'          => new external_value(PARAM_INT,   'Time limit in seconds (0 = no limit, -1 = no change)', VALUE_DEFAULT, -1),
            'attempts'           => new external_value(PARAM_INT,   'Max attempts (0 = unlimited, -1 = no change)', VALUE_DEFAULT, -1),
            'grademethod'        => new external_value(PARAM_INT,   '1=highest, 2=average, 3=first, 4=last (-1 = no change)', VALUE_DEFAULT, -1),
            'visible'            => new external_value(PARAM_INT,   '1 = visible, 0 = hidden, -1 = no change', VALUE_DEFAULT, -1),
            'shuffleanswers'     => new external_value(PARAM_INT,   '1 = shuffle, 0 = fixed, -1 = no change', VALUE_DEFAULT, -1),
            'questionsperpage'   => new external_value(PARAM_INT,   'Questions per page (-1 = no change)', VALUE_DEFAULT, -1),
        ]);
    }

    public static function execute(
        int $cmid,
        string $name = '',
        string $intro = '',
        int $timelimit = -1,
        int $attempts = -1,
        int $grademethod = -1,
        int $visible = -1,
        int $shuffleanswers = -1,
        int $questionsperpage = -1
    ): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'             => $cmid,
            'name'             => $name,
            'intro'            => $intro,
            'timelimit'        => $timelimit,
            'attempts'         => $attempts,
            'grademethod'      => $grademethod,
            'visible'          => $visible,
            'shuffleanswers'   => $shuffleanswers,
            'questionsperpage' => $questionsperpage,
        ]);

        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $coursecontext = context_course::instance($cm->course);
        $modulecontext = context_module::instance($cm->id);
        self::validate_context($modulecontext);
        if (!has_capability('mod/quiz:manage', $modulecontext)
                && !has_capability('moodle/course:manageactivities', $coursecontext)) {
            throw new \required_capability_exception($modulecontext, 'mod/quiz:manage', 'nopermissions', '');
        }

        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

        if ($params['name'] !== '')           $quiz->name             = $params['name'];
        if ($params['intro'] !== '')          $quiz->intro            = $params['intro'];
        if ($params['timelimit'] >= 0)        $quiz->timelimit        = $params['timelimit'];
        if ($params['attempts'] >= 0)         $quiz->attempts         = $params['attempts'];
        if ($params['grademethod'] >= 0)      $quiz->grademethod      = $params['grademethod'];
        if ($params['shuffleanswers'] >= 0)   $quiz->shuffleanswers   = $params['shuffleanswers'];
        if ($params['questionsperpage'] >= 0) $quiz->questionsperpage = $params['questionsperpage'];
        $quiz->timemodified = time();
        $DB->update_record('quiz', $quiz);

        if ($params['visible'] >= 0) {
            set_coursemodule_visible($cm->id, $params['visible'] ? 1 : 0);
        }

        rebuild_course_cache($cm->course, true);

        return ['cmid' => $params['cmid'], 'message' => 'Quiz updated successfully.'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
