<?php
/**
 * Services definition for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'mod_paper_check_status' => [
        'classname'   => 'mod_paper\external\external_api',
        'methodname'  => 'check_status',
        'description' => 'Check the processing status of evaluations',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'mod_paper_update_eval_item' => [
        'classname'   => 'mod_paper\external\external_api',
        'methodname'  => 'update_eval_item',
        'description' => 'Update an evaluation item with grade and feedback',
        'type'        => 'write',
        'ajax'        => true,
    ],
];
