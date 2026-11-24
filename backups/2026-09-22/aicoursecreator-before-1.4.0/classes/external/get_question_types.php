<?php
// This file is part of Moodle - http://moodle.org/

namespace local_aicoursecreator\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;

class get_question_types extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID for capability check'),
        ]);
    }

    public static function execute(int $courseid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
        ]);

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('moodle/question:viewall', $context);

        $types = [];
        foreach (\question_bank::get_all_qtypes() as $name => $qtype) {
            if ($name === 'missingtype') {
                continue;
            }

            $types[] = [
                'name' => $name,
                'pluginname' => get_string('pluginname', 'qtype_' . $name),
                'usable' => method_exists($qtype, 'can_use') ? (int) $qtype->can_use() : 1,
            ];
        }

        usort($types, function(array $a, array $b): int {
            return strcmp($a['name'], $b['name']);
        });

        return $types;
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'name'       => new external_value(PARAM_PLUGIN, 'Question type name for Moodle XML, e.g. multichoice'),
                'pluginname' => new external_value(PARAM_TEXT, 'Human-readable plugin name'),
                'usable'     => new external_value(PARAM_INT, '1 if the type can be used to create questions'),
            ])
        );
    }
}
