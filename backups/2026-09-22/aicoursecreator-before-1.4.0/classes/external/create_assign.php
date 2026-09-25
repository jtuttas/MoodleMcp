<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;

/**
 * Creates a mod_assign activity inside a given section.
 */
class create_assign extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'        => new external_value(PARAM_INT,  'Course ID'),
            'sectionnum'      => new external_value(PARAM_INT,  'Section number (0-based)'),
            'name'            => new external_value(PARAM_TEXT, 'Assignment title'),
            'description'     => new external_value(PARAM_RAW,  'Assignment description (HTML)', VALUE_DEFAULT, ''),
            'duedate'         => new external_value(PARAM_INT,  'Due date as Unix timestamp (0 = no due date)', VALUE_DEFAULT, 0),
            'allowsubmissionsfromdate' => new external_value(PARAM_INT, 'Allow submissions from (Unix timestamp, 0 = always)', VALUE_DEFAULT, 0),
            'maxfiles'        => new external_value(PARAM_INT,  'Max number of uploaded files (0 = no file upload)', VALUE_DEFAULT, 1),
            'submissiondrafts' => new external_value(PARAM_INT, 'Require students to click Submit (1) or auto-submit (0)', VALUE_DEFAULT, 0),
            'visible'         => new external_value(PARAM_INT,  'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(
        int    $courseid,
        int    $sectionnum,
        string $name,
        string $description = '',
        int    $duedate = 0,
        int    $allowsubmissionsfromdate = 0,
        int    $maxfiles = 1,
        int    $submissiondrafts = 0,
        int    $visible = 1
    ): array {
        global $DB, $CFG;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'                 => $courseid,
            'sectionnum'               => $sectionnum,
            'name'                     => $name,
            'description'              => $description,
            'duedate'                  => $duedate,
            'allowsubmissionsfromdate' => $allowsubmissionsfromdate,
            'maxfiles'                 => $maxfiles,
            'submissiondrafts'         => $submissiondrafts,
            'visible'                  => $visible,
        ]);

        // Check permissions.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Get the course.
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);

        // Build module info.
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename   = 'assign';
        $moduleinfo->module       = $DB->get_field('modules', 'id', ['name' => 'assign'], MUST_EXIST);
        $moduleinfo->course       = $params['courseid'];
        $moduleinfo->section      = $params['sectionnum'];
        $moduleinfo->name         = $params['name'];
        $moduleinfo->visible      = $params['visible'];

        // Intro / description.
        $moduleinfo->intro        = $params['description'];
        $moduleinfo->introformat  = FORMAT_HTML;

        // Dates.
        $moduleinfo->allowsubmissionsfromdate         = $params['allowsubmissionsfromdate'];
        $moduleinfo->duedate                          = $params['duedate'];
        $moduleinfo->cutoffdate                       = 0;
        $moduleinfo->gradingduedate                   = 0;

        // Submission settings.
        $moduleinfo->submissiondrafts                 = $params['submissiondrafts'];
        $moduleinfo->requiresubmissionstatement       = 0;
        $moduleinfo->sendnotifications                = 0;
        $moduleinfo->sendlatenotifications            = 0;
        $moduleinfo->sendstudentnotifications         = 1;

        // Submission plugins: file + online text.
        $moduleinfo->assignsubmission_onlinetext_enabled = 1;
        $moduleinfo->assignsubmission_file_enabled       = ($params['maxfiles'] > 0) ? 1 : 0;
        $moduleinfo->assignsubmission_file_maxfiles      = $params['maxfiles'];
        $moduleinfo->assignsubmission_file_maxsizebytes  = 0; // Use course default.

        // Feedback plugins.
        $moduleinfo->assignfeedback_comments_enabled  = 1;
        $moduleinfo->assignfeedback_editpdf_enabled   = 0;

        // Grading.
        $moduleinfo->grade                            = 100;
        $moduleinfo->gradepass                        = 0;
        $moduleinfo->gradecat                         = 0;

        // Team submissions off.
        $moduleinfo->teamsubmission                   = 0;
        $moduleinfo->requireallteammemberssubmit      = 0;
        $moduleinfo->teamsubmissiongroupingid         = 0;
        $moduleinfo->blindmarking                     = 0;
        $moduleinfo->attemptreopenmethod              = 'none';
        $moduleinfo->maxattempts                      = -1;
        $moduleinfo->markingworkflow                  = 0;
        $moduleinfo->markingallocation                = 0;

        // Add the module to the course.
        $moduleinfo = add_moduleinfo($moduleinfo, $course);

        return [
            'cmid'    => (int) $moduleinfo->coursemodule,
            'message' => 'Assignment "' . $params['name'] . '" successfully created.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID of the created assignment'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
