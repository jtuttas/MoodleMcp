<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;

class get_courses extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(PARAM_TEXT, 'Optional course name/idnumber search text', VALUE_DEFAULT, ''),
            'limit'  => new external_value(PARAM_INT, 'Maximum number of courses to return', VALUE_DEFAULT, 50),
        ]);
    }

    public static function execute(string $search = '', int $limit = 50): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'limit'  => $limit,
        ]);

        $limit = max(1, min(200, $params['limit']));
        $sqlparams = [];
        $where = 'id <> :siteid';
        $sqlparams['siteid'] = SITEID;

        if ($params['search'] !== '') {
            $where .= ' AND (' .
                $DB->sql_like('fullname', ':searchfullname', false, false) .
                ' OR ' . $DB->sql_like('shortname', ':searchshortname', false, false) .
                ' OR ' . $DB->sql_like('idnumber', ':searchidnumber', false, false) .
                ')';
            $searchparam = '%' . $DB->sql_like_escape($params['search']) . '%';
            $sqlparams['searchfullname'] = $searchparam;
            $sqlparams['searchshortname'] = $searchparam;
            $sqlparams['searchidnumber'] = $searchparam;
        }

        $records = $DB->get_records_select(
            'course',
            $where,
            $sqlparams,
            'fullname ASC',
            'id, fullname, shortname, idnumber, visible, startdate, enddate',
            0,
            $limit
        );

        $result = [];
        foreach ($records as $course) {
            $context = context_course::instance($course->id, IGNORE_MISSING);
            if (!$context || !has_capability('moodle/course:view', $context)) {
                continue;
            }

            $result[] = [
                'id'        => (int) $course->id,
                'fullname'  => $course->fullname,
                'shortname' => $course->shortname,
                'idnumber'  => $course->idnumber,
                'visible'   => (int) $course->visible,
                'startdate' => (int) $course->startdate,
                'enddate'   => (int) $course->enddate,
            ];
        }

        return $result;
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id'        => new external_value(PARAM_INT,  'Course ID'),
                'fullname'  => new external_value(PARAM_TEXT, 'Full course name'),
                'shortname' => new external_value(PARAM_TEXT, 'Short course name'),
                'idnumber'  => new external_value(PARAM_RAW,  'Course idnumber'),
                'visible'   => new external_value(PARAM_INT,  'Visible (1) or hidden (0)'),
                'startdate' => new external_value(PARAM_INT,  'Course start date timestamp'),
                'enddate'   => new external_value(PARAM_INT,  'Course end date timestamp'),
            ])
        );
    }
}
