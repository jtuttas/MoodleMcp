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
require_once($CFG->dirroot . '/mod/page/lib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_course;

/**
 * Creates a mod_page activity inside a given section.
 */
class create_page extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'   => new external_value(PARAM_INT,  'Course ID'),
            'sectionnum' => new external_value(PARAM_INT,  'Section number (0-based)'),
            'name'       => new external_value(PARAM_TEXT, 'Page title'),
            'content'    => new external_value(PARAM_RAW,  'Page HTML content'),
            'visible'    => new external_value(PARAM_INT,  'Visible (1) or hidden (0)', VALUE_DEFAULT, 1),
        ]);
    }

    public static function execute(int $courseid, int $sectionnum, string $name, string $content, int $visible = 1): array {
        global $DB, $CFG;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'   => $courseid,
            'sectionnum' => $sectionnum,
            'name'       => $name,
            'content'    => $content,
            'visible'    => $visible,
        ]);

        // Check permissions.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        // Get the course.
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);

        // Build the module data object (mirrors what the form submission produces).
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename   = 'page';
        $moduleinfo->module       = $DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
        $moduleinfo->course       = $params['courseid'];
        $moduleinfo->section      = $params['sectionnum'];
        $moduleinfo->name         = $params['name'];
        $moduleinfo->visible      = $params['visible'];
        $moduleinfo->intro        = '';
        $moduleinfo->introformat  = FORMAT_HTML;
        $moduleinfo->content      = $params['content'];
        $moduleinfo->contentformat = FORMAT_HTML;
        $moduleinfo->display      = 5; // Display in page.
        $moduleinfo->printheading = 1;
        $moduleinfo->printintro   = 0;
        $moduleinfo->printlastmodified = 1;

        // Add the module to the course.
        $moduleinfo = add_moduleinfo($moduleinfo, $course);

        return [
            'cmid'    => (int) $moduleinfo->coursemodule,
            'message' => 'Page "' . $params['name'] . '" successfully created.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cmid'    => new external_value(PARAM_INT,  'Course module ID of the created page'),
            'message' => new external_value(PARAM_TEXT, 'Success message'),
        ]);
    }
}
