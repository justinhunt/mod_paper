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
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$paper = $DB->get_record('paper', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/paper:manage', $context);

$PAGE->set_url('/mod/paper/presets.php', array('id' => $cm->id));
$PAGE->set_title(get_string('managepresets', 'mod_paper'));
$PAGE->set_heading($course->fullname);

$baseurl = new moodle_url('/mod/paper/presets.php', ['id' => $id]);

if ($action === 'delete' && $presetid > 0 && confirm_sesskey()) {
    $DB->delete_records('paper_grading_presets', ['id' => $presetid, 'userid' => $USER->id]);
    redirect($baseurl, get_string('presetdeleted', 'mod_paper'));
}

$mform = new \mod_paper\form\preset_form($baseurl->out(false, ['action' => 'edit']));

if ($mform->is_cancelled()) {
    redirect($baseurl);
} else if ($data = $mform->get_data()) {
    $record = new stdClass();
    $record->name = $data->name;
    $record->content = $data->content;
    $record->userid = $USER->id;
    $record->timemodified = time();

    if ($data->presetid) {
        $record->id = $data->presetid;
        $DB->update_record('paper_grading_presets', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('paper_grading_presets', $record);
    }
    redirect($baseurl, get_string('presetsaved', 'mod_paper'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managepresets', 'mod_paper'));

if ($action === 'edit' || $action === 'add') {
    if ($presetid > 0) {
        $preset = $DB->get_record('paper_grading_presets', ['id' => $presetid, 'userid' => $USER->id], '*', MUST_EXIST);
        $preset->presetid = $preset->id;
        $mform->set_data($preset);
    }
    $mform->display();
} else {
    // List presets
    echo html_writer::link($baseurl->out(false, ['action' => 'add']), 
        $OUTPUT->pix_icon('t/add', get_string('addpreset', 'mod_paper')) . ' ' . get_string('addpreset', 'mod_paper'), 
        ['class' => 'btn btn-primary mb-3']);

    $presets = $DB->get_records('paper_grading_presets', ['userid' => $USER->id], 'name ASC');

    if (empty($presets)) {
        echo $OUTPUT->notification(get_string('nopresets', 'mod_paper'), 'info');
    } else {
        $table = new html_table();
        $table->head = [get_string('presetname', 'mod_paper'), get_string('presetcontent', 'mod_paper'), ''];
        $table->data = [];

        foreach ($presets as $p) {
            $editurl = $baseurl->out(false, ['action' => 'edit', 'presetid' => $p->id]);
            $deleteurl = $baseurl->out(false, ['action' => 'delete', 'presetid' => $p->id, 'sesskey' => sesskey()]);

            $actions = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit'))) . ' ';
            $actions .= html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', get_string('delete')), 
                ['onclick' => 'return confirm("' . get_string('confirmdeletepreset', 'mod_paper') . '")']);

            $table->data[] = [
                format_string($p->name),
                nl2br(format_text($p->content, FORMAT_PLAIN)),
                $actions
            ];
        }
        echo html_writer::table($table);
    }
    
    echo html_writer::link(new moodle_url('/mod/paper/view.php', ['id' => $id]), 
        get_string('back'), ['class' => 'btn btn-secondary mt-3']);
}

echo $OUTPUT->footer();
