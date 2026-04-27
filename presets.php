<?php
/**
 * Presets Management for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/paper/lib.php');

$id = required_param('id', PARAM_INT); // Course module ID
$action = optional_param('action', 'list', PARAM_ALPHA);
$presetid = optional_param('presetid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('paper', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$paper = $DB->get_record('paper', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/paper:manage', $context);

$PAGE->set_url('/mod/paper/presets.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('managepresets', 'mod_paper'));
$PAGE->set_heading($course->fullname);

$baseurl = new moodle_url('/mod/paper/presets.php', ['id' => $id]);

if ($action === 'delete' && $presetid > 0 && confirm_sesskey()) {
    $DB->delete_records('paper_grading_presets', ['id' => $presetid]);
    redirect($baseurl, get_string('presetdeleted', 'mod_paper'));
}

if ($action === 'apply' && $presetid > 0 && confirm_sesskey()) {
    $preset = $DB->get_record('paper_grading_presets', ['id' => $presetid], '*', MUST_EXIST);
    $DB->set_field('paper', 'gradingpreset', $preset->content, ['id' => $paper->id]);
    redirect(new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]), get_string('presetapplied', 'mod_paper'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managepresetsfor', 'mod_paper', format_string($paper->name)));

echo $OUTPUT->box(get_string('presets_help', 'mod_paper'), 'info mb-3');

if ($action === 'edit' || $action === 'add') {
    if ($presetid > 0) {
        $preset = $DB->get_record('paper_grading_presets', ['id' => $presetid]);
        $data = $preset;
    } else {
        $data = new stdClass();
    }
    
    $mform = new \mod_paper\form\preset_form(null, ['action' => $action, 'presetid' => $presetid]);
    $mform->set_data($data);
    
    if ($mform->is_cancelled()) {
        redirect($baseurl);
    } else if ($formdata = $mform->get_data()) {
        if ($action === 'add') {
            $formdata->timecreated = time();
            $formdata->timemodified = time();
            $DB->insert_record('paper_grading_presets', $formdata);
        } else {
            $formdata->id = $presetid;
            $formdata->timemodified = time();
            $DB->update_record('paper_grading_presets', $formdata);
        }
        redirect($baseurl);
    }
    
    $mform->display();
} else {
    // List presets
    $presets = $DB->get_records('paper_grading_presets', [], 'name ASC');
    
    if (empty($presets)) {
        echo $OUTPUT->notification(get_string('nopresetsfound', 'mod_paper'), 'info');
    } else {
        $table = new html_table();
        $table->head = [get_string('presetid', 'mod_paper'), get_string('presetname', 'mod_paper'), get_string('actions', 'mod_paper')];
        
        foreach ($presets as $p) {
            $applyurl = new moodle_url($baseurl, ['action' => 'apply', 'presetid' => $p->id, 'sesskey' => sesskey()]);
            $editurl = new moodle_url($baseurl, ['action' => 'edit', 'presetid' => $p->id]);
            $deleteurl = new moodle_url($baseurl, ['action' => 'delete', 'presetid' => $p->id, 'sesskey' => sesskey()]);
            
            $applylink = html_writer::link($applyurl, get_string('apply', 'mod_paper'), [
                'class' => 'btn btn-primary btn-sm mr-2',
                'onclick' => "return confirm('" . get_string('applypresetconfirm', 'mod_paper') . "');"
            ]);
            $editlink = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')), ['class' => 'mr-2']);
            $deletelink = html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', get_string('delete')), [
                'class' => 'text-danger',
                'onclick' => "return confirm('" . get_string('deletepresetconfirm', 'mod_paper') . "');"
            ]);
            
            $table->data[] = [$p->id, format_string($p->name), $applylink . $editlink . $deletelink];
        }
        echo html_writer::table($table);
    }
    
    $addurl = new moodle_url($baseurl, ['action' => 'add']);
    echo html_writer::link($addurl, get_string('addnewpreset', 'mod_paper'), ['class' => 'btn btn-secondary']);
}

$viewurl = new moodle_url('/mod/paper/view.php', ['id' => $cm->id]);
echo html_writer::link($viewurl, get_string('returntotop', 'mod_paper'), ['class' => 'btn btn-link d-block mt-3']);

echo $OUTPUT->footer();
