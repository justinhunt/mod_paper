<?php
/**
 * View Script for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course module ID

$cm = get_coursemodule_from_id('paper', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$paper = $DB->get_record('paper', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/paper/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($paper->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($paper->name));

echo $OUTPUT->box(get_string('view_help', 'mod_paper'), 'info mb-3');

$templatecontext = [
    'ismanager' => has_capability('mod/paper:manage', $context),
    'hasareas' => $DB->record_exists('paper_response_areas', ['paperid' => $paper->id]),
    'setupurl' => (new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]))->out(false),
    'processurl' => (new moodle_url('/mod/paper/process_submissions.php', ['id' => $cm->id]))->out(false),
    'reportsurl' => (new moodle_url('/mod/paper/reports.php', ['id' => $cm->id]))->out(false),
    'presetsurl' => (new moodle_url('/mod/paper/presets.php', ['id' => $cm->id]))->out(false),
    'evalcount' => $DB->count_records('paper_evaluations', ['paperid' => $paper->id]),
];

echo $OUTPUT->render_from_template('mod_paper/view_page', $templatecontext);

echo $OUTPUT->footer();
