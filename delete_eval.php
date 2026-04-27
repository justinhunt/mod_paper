<?php
/**
 * Delete Evaluation Script for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course module ID
$evalid = required_param('evalid', PARAM_INT); // Evaluation ID

$cm = get_coursemodule_from_id('paper', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$paper = $DB->get_record('paper', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
require_capability('mod/paper:manage', context_module::instance($cm->id));
require_sesskey();

// Ensure the evaluation belongs to this paper instance
$evaluation = $DB->get_record('paper_evaluations', ['id' => $evalid, 'paperid' => $paper->id], '*', MUST_EXIST);

// Delete the items and the evaluation
$DB->delete_records('paper_eval_items', ['evalid' => $evaluation->id]);
$DB->delete_records('paper_evaluations', ['id' => $evaluation->id]);

\core\notification::success("Evaluation deleted successfully.");
redirect(new moodle_url('/mod/paper/reports.php', ['id' => $cm->id]));
