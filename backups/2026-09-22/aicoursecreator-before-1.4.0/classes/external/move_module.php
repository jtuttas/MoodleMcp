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

class move_module extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'       => new external_value(PARAM_INT, 'Course module ID to move'),
            'sectionnum' => new external_value(PARAM_INT, 'Target section number (0-based). -1 = keep current section, only reorder', VALUE_DEFAULT, -1),
            'beforecmid' => new external_value(PARAM_INT, 'cmid of the activity to insert before. 0 = move to the end of the section', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute(int $cmid, int $sectionnum = -1, int $beforecmid = 0): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid'       => $cmid,
            'sectionnum' => $sectionnum,
            'beforecmid' => $beforecmid,
        ]);

        $cm = get_coursemodule_from_id('', $params['cmid'], 0, false, MUST_EXIST);

        $context = context_course::instance($cm->course);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        if ($params['sectionnum'] === -1) {
            // Keep current section: get_coursemodule_from_id() does not populate a
            // 'sectionnum' property, only 'section' (the course_sections.id foreign key),
            // so resolve the section by id directly rather than guessing a section number.
            $section = $DB->get_record('course_sections', ['id' => $cm->section], '*', MUST_EXIST);
            $targetsectionnum = (int) $section->section;
        } else {
            $targetsectionnum = $params['sectionnum'];
            if ($targetsectionnum < 0) {
                throw new \invalid_parameter_exception('sectionnum must be 0 or greater (or -1 to keep the current section).');
            }
            $section = $DB->get_record('course_sections',
                    ['course' => $cm->course, 'section' => $targetsectionnum], '*', IGNORE_MISSING);
            if (!$section) {
                course_create_sections_if_missing($cm->course, $targetsectionnum);
                $section = $DB->get_record('course_sections',
                        ['course' => $cm->course, 'section' => $targetsectionnum], '*', MUST_EXIST);
            }
        }

        $beforemod = null;
        if (!empty($params['beforecmid'])) {
            $beforemod = $DB->get_record('course_modules', ['id' => $params['beforecmid']], '*', MUST_EXIST);
            if ((int) $beforemod->course !== (int) $cm->course) {
                throw new \invalid_parameter_exception('beforecmid belongs to another course.');
            }
        }

        $modvisible = moveto_module($cm, $section, $beforemod);

        rebuild_course_cache($cm->course, true);

        return [
            'cmid'       => (int) $cm->id,
            'sectionnum' => (int) $targetsectionnum,
            'sectionid'  => (int) $section->id,
            'visible'    => (int) $modvisible,
            'message'    => 'Module moved successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'       => new external_value(PARAM_INT, 'Course module ID that was moved'),
            'sectionnum' => new external_value(PARAM_INT, 'Section number the module now belongs to'),
            'sectionid'  => new external_value(PARAM_INT, 'Section ID the module now belongs to'),
            'visible'    => new external_value(PARAM_INT, 'Resulting visibility (0/1) after the move'),
            'message'    => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
