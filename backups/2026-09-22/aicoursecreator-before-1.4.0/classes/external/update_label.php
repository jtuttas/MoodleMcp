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

class update_label extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'name'    => new external_value(PARAM_TEXT, 'New display name in course management (empty = no change)', VALUE_DEFAULT, ''),
            'content' => new external_value(PARAM_RAW,  'New HTML content (empty = no change)', VALUE_DEFAULT, ''),
            'visible' => new external_value(PARAM_INT,  '1 = visible, 0 = hidden, -1 = no change', VALUE_DEFAULT, -1),
        ]);
    }

    public static function execute(int $cmid, string $name = '', string $content = '', int $visible = -1): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid, 'name' => $name, 'content' => $content, 'visible' => $visible,
        ]);

        $cm = get_coursemodule_from_id('label', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Name und/oder Content in mdl_label updaten
        if ($params['name'] !== '' || $params['content'] !== '') {
            $label = $DB->get_record('label', ['id' => $cm->instance], '*', MUST_EXIST);
            if ($params['name'] !== '')    $label->name         = $params['name'];
            if ($params['content'] !== '') $label->intro        = $params['content'];
            $label->timemodified = time();
            $DB->update_record('label', $label);
        }

        if ($params['visible'] >= 0) {
            $DB->set_field('course_modules', 'visible', $params['visible'], ['id' => $cm->id]);
        }

        try {
            rebuild_course_cache($cm->course, true);
        } catch (\Exception $e) {
            // Cache-Fehler ignorieren
        }

        return ['cmid' => $params['cmid'], 'message' => 'Label updated successfully.'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
