<?php
/**
 * Reports Script for mod_paper
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
require_capability('mod/paper:manage', $context);

$PAGE->set_url('/mod/paper/reports.php', array('id' => $cm->id));
$PAGE->set_title("Reports");
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading("Evalaution Reports for: " . format_string($paper->name));

$evaluations = $DB->get_records('paper_evaluations', ['paperid' => $paper->id]);

if (empty($evaluations)) {
    echo $OUTPUT->notification("No evaluations found.");
} else {
    $table = new html_table();
    $table->head = ['ID', 'Student Name', 'Total Grade', 'Actions'];
    
    foreach ($evaluations as $eval) {
        $viewurl = new moodle_url('/mod/paper/view_eval.php', ['id' => $cm->id, 'evalid' => $eval->id]);
        $deleteurl = new moodle_url('/mod/paper/delete_eval.php', ['id' => $cm->id, 'evalid' => $eval->id, 'sesskey' => sesskey()]);
        
        // Check if evaluation is pending
        $sql = "SELECT COUNT(pei.id) 
                FROM {paper_eval_items} pei
                JOIN {paper_response_areas} pra ON pra.id = pei.responseareaid
                WHERE pei.evalid = :evalid AND pra.isnamefield = 0 AND pei.correctedtext = '' AND pra.grammarcorrections != 'no'";
        $pendingcount = $DB->count_records_sql($sql, ['evalid' => $eval->id]);
        
        if ($pendingcount == 0) {
            $viewlink = html_writer::link($viewurl, $OUTPUT->pix_icon('t/preview', 'View Evaluation'), ['class' => 'mr-2']);
        } else {
            $viewlink = html_writer::tag('span', '...', ['class' => 'text-muted mr-2', 'title' => 'Evaluation Pending...']);
        }

        $individualdownloadurl = moodle_url::make_pluginfile_url($context->id, 'mod_paper', 'downloadevaluations', $eval->id, '/', 'evaluation.pdf');
        $downloadlink = html_writer::link($individualdownloadurl, $OUTPUT->pix_icon('f/pdf', 'Download PDF'), ['target' => '_blank', 'class' => 'mr-2']);

        $deletelink = html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', 'Delete'), [
            'onclick' => "return confirm('Are you sure you want to delete this evaluation?');",
            'class' => 'text-danger'
        ]);

        $table->data[] = [
            $eval->id,
            $eval->studentnametext,
            $eval->totalgrade,
            $viewlink . $downloadlink . $deletelink
        ];
    }
    
    echo html_writer::table($table);
}

// Button to generate combined PDF
$downloadurl = moodle_url::make_pluginfile_url($context->id, 'mod_paper', 'downloadevaluations', 0, '/', 'evaluations.pdf');
$reevaluateurl = new moodle_url('/mod/paper/re_evaluate.php', ['id' => $cm->id, 'sesskey' => sesskey()]);
$deleteallurl = new moodle_url('/mod/paper/delete_all_evals.php', ['id' => $cm->id, 'sesskey' => sesskey()]);

$buttons = html_writer::link($downloadurl, 'View All Combined PDFs', ['class' => 'btn btn-primary mr-2', 'target' => '_blank']);
$buttons .= html_writer::link($reevaluateurl, 'Re-evaluate All', ['class' => 'btn btn-warning mr-2', 'onclick' => "return confirm('Are you sure you want to clear all existing grammar corrections and re-evaluate them?');"]);
$buttons .= html_writer::link($deleteallurl, 'Delete All Submissions', ['class' => 'btn btn-danger', 'onclick' => "return confirm('Are you sure you want to delete ALL evaluations? This cannot be undone.');"]);

echo html_writer::tag('div', $buttons, ['class' => 'mt-3 mb-3']);

$viewurl = new moodle_url('/mod/paper/view.php', ['id' => $cm->id]);
echo html_writer::link($viewurl, 'Return to Top', ['class' => 'btn btn-secondary mt-2 mb-3']);

echo $OUTPUT->footer();
