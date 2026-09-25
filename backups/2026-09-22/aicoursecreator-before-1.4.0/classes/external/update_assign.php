<?php
namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_module;

class update_assign extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'        => new external_value(PARAM_INT,  'Course module ID'),
            'name'        => new external_value(PARAM_TEXT, 'New assignment title', VALUE_DEFAULT, ''),
            'description' => new external_value(PARAM_RAW,  'New HTML description', VALUE_DEFAULT, ''),
            'duedate'     => new external_value(PARAM_INT,  'New due date as Unix timestamp (-1 = no change)', VALUE_DEFAULT, -1),
            'visible'     => new external_value(PARAM_INT,  '1 = visible, 0 = hidden, -1 = no change', VALUE_DEFAULT, -1),
        ]);
    }

    public static function execute(int $cmid, string $name = '', string $description = '', int $duedate = -1, int $visible = -1): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid, 'name' => $name, 'description' => $description,
            'duedate' => $duedate, 'visible' => $visible,
        ]);

        $cm = get_coursemodule_from_id('assign', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Update mdl_assign record (name, intro, duedate alle hier)
        $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST);
        if ($params['name'] !== '')        $assign->name    = $params['name'];
        if ($params['description'] !== '') $assign->intro   = $params['description'];
        if ($params['duedate'] >= 0)       $assign->duedate = $params['duedate'];
        $assign->timemodified = time();
        $DB->update_record('assign', $assign);

        // course_modules: nur visible updaten, wenn explizit gesetzt
        if ($params['visible'] >= 0) {
            $DB->set_field('course_modules', 'visible', $params['visible'], ['id' => $cm->id]);
        }

        rebuild_course_cache($cm->course, true);

        return ['cmid' => $params['cmid'], 'message' => 'Assignment updated successfully.'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
