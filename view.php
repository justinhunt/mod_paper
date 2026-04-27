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

if (has_capability('mod/paper:manage', $context)) {
    echo $OUTPUT->box_start('generalbox');
    
    // Check if setup is done
    $has_areas = $DB->record_exists('paper_response_areas', ['paperid' => $paper->id]);
    
    if (!$has_areas) {
        $setupurl = new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]);
        echo html_writer::tag('div', 'Setup is not complete. Please identify response areas first.', ['class' => 'alert alert-warning']);
        echo html_writer::link($setupurl, get_string('viewsetup', 'mod_paper'), ['class' => 'btn btn-primary']);
    } else {
        $setupurl = new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]);
        echo html_writer::link($setupurl, get_string('viewsetup', 'mod_paper'), ['class' => 'btn btn-secondary mr-2']);
        
        $processurl = new moodle_url('/mod/paper/process_submissions.php', ['id' => $cm->id]);
        echo html_writer::link($processurl, get_string('uploadsubmissions', 'mod_paper'), ['class' => 'btn btn-primary mr-2']);
        
        $reportsurl = new moodle_url('/mod/paper/reports.php', ['id' => $cm->id]);
        echo html_writer::link($reportsurl, get_string('viewreports', 'mod_paper'), ['class' => 'btn btn-info mr-2']);

        $presetsurl = new moodle_url('/mod/paper/presets.php', ['id' => $cm->id]);
        echo html_writer::link($presetsurl, get_string('managepresets', 'mod_paper'), ['class' => 'btn btn-outline-primary']);
        
        // Show summary
        $evalcount = $DB->count_records('paper_evaluations', ['paperid' => $paper->id]);
        echo html_writer::tag('p', "Total evaluations processed: {$evalcount}", ['class' => 'mt-3']);
    }
    
    echo $OUTPUT->box_end();
} else {
    // Student view
    echo $OUTPUT->box("This activity is managed by your teacher. Evaluations will be available here when completed.");
    // Students would see their own evaluations here in a complete implementation.
}

echo $OUTPUT->footer();
