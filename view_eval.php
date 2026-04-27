<?php
/**
 * View Single Evaluation Script for mod_paper
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
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/paper/view_eval.php', array('id' => $cm->id, 'evalid' => $evalid));
$PAGE->set_title("View Evaluation");
$PAGE->set_heading(format_string($course->fullname));

// Ensure requested eval belongs to this paper instance
$currenteval = $DB->get_record('paper_evaluations', ['id' => $evalid, 'paperid' => $paper->id], '*', MUST_EXIST);

// Calculate total possible grade
$maxpossible = $DB->get_field_sql("SELECT SUM(maxgrade) FROM {paper_response_areas} WHERE paperid = :paperid AND isnamefield = 0", ['paperid' => $paper->id]);
$maxpossible = round($maxpossible, 2) + 0;

echo $OUTPUT->header();

$studentname = !empty($currenteval->studentnametext) ? $currenteval->studentnametext : 'Unknown Student';
echo $OUTPUT->heading("Reviewing Evaluation: " . format_string($studentname));

// Pagination logic
$evalset = $DB->get_records('paper_evaluations', ['paperid' => $paper->id], 'id ASC', 'id');
$evalids = array_keys($evalset);
$currentindex = array_search($evalid, $evalids);

echo html_writer::tag('style', '
    .eval-item-area:hover { outline: 4px solid red !important; background-color: rgba(255, 0, 0, 0.1) !important; z-index: 20; }
    #edit-sidebar { transition: all 0.3s ease; box-shadow: -2px 0 5px rgba(0,0,0,0.1); }
    #view-eval-page-wrapper { align-items: flex-start; }
    .eval-item-area { transition: outline 0.2s ease; }
');

echo html_writer::start_tag('div', ['class' => 'd-flex justify-content-between mb-3']);
if ($currentindex > 0) {
    $prevalid = $evalids[$currentindex - 1];
    $prevurl = new moodle_url('/mod/paper/view_eval.php', ['id' => $cm->id, 'evalid' => $prevalid]);
    echo html_writer::link($prevurl, '&laquo; Previous Student', ['class' => 'btn btn-secondary']);
} else {
    echo html_writer::tag('span', '');
}
$backurl = new moodle_url('/mod/paper/reports.php', ['id' => $cm->id]);
echo html_writer::link($backurl, 'Return to Reports', ['class' => 'btn btn-outline-primary']);

if ($currentindex < count($evalids) - 1) {
    $nextevalid = $evalids[$currentindex + 1];
    $nexturl = new moodle_url('/mod/paper/view_eval.php', ['id' => $cm->id, 'evalid' => $nextevalid]);
    echo html_writer::link($nexturl, 'Next Student &raquo;', ['class' => 'btn btn-secondary']);
} else {
    echo html_writer::tag('span', '');
}
echo html_writer::end_tag('div');

// UI Canvas wrapper
$fs = get_file_storage();
$file = $fs->get_file($context->id, 'mod_paper', 'template', 0, '/', 'template.jpg');

if ($file) {
    $imageurl = moodle_url::make_pluginfile_url($context->id, 'mod_paper', 'template', 0, '/', 'template.jpg');
    
    // Add Flex container for image and sidebar
    echo html_writer::start_tag('div', ['class' => 'd-flex flex-wrap', 'id' => 'view-eval-page-wrapper', 'style' => 'max-width: 1200px; margin: 0 auto;']);
    
    // Left side: Template
    echo html_writer::start_tag('div', [
        'id' => 'template-container',
        'style' => 'position: relative; flex: 1; min-width: 600px; border: 1px solid #ccc; background: #eee;'
    ]);
    
    // Background original clean template
    echo html_writer::empty_tag('img', [
        'src' => $imageurl,
        'style' => 'width: 100%; height: auto; display: block;'
    ]);
    
    $areas = $DB->get_records('paper_response_areas', ['paperid' => $paper->id], 'id ASC');
    $items = $DB->get_records('paper_eval_items', ['evalid' => $evalid]);
    
    // Group items by responseareaid for easier lookup
    $itemsbyarea = [];
    foreach ($items as $item) {
        $itemsbyarea[$item->responseareaid] = $item;
    }

    // Draw the OCR text blocks physically onto the UI
    $bottommost_y = 0;
    foreach ($areas as $area) {
        $item = $itemsbyarea[$area->id] ?? null;
        
        $itemid = $item ? $item->id : 0;
        $ocr = $item ? $item->ocrtext : '';
        $corrected = $item ? $item->correctedtext : '';
        $feedback = $item ? $item->feedback : '';
        $grade = $item ? $item->itemgrade : null;
        
        $bottom = $area->box_y + $area->box_h;
        if ($bottom > $bottommost_y) {
            $bottommost_y = $bottom;
        }
        
        $targetfontcss = \mod_paper\utils::get_css_font_family($paper->targetlanguagefont ?? 'courier');
        
        // Match identical CSS math from setup canvas
        $valign = $area->isnamefield ? 'justify-content: flex-end;' : 'justify-content: flex-start;';
        $style = sprintf(
            'position: absolute; left: %s%%; top: %s%%; width: %s%%; height: %s%%; outline: 2px solid blue; background-color: rgba(0, 0, 255, 0.1); color: black; font-weight: normal; padding: 4px; box-sizing: border-box; overflow: visible; font-family: %s; cursor: pointer; display: flex; flex-direction: column; %s',
            $area->box_x, $area->box_y, $area->box_w, $area->box_h, $targetfontcss, $valign
        );

        if (!empty($feedback) && !$area->isnamefield && ($area->feedbackmode ?? 'none') !== 'none') {
            $feedbackfontcss = \mod_paper\utils::get_css_font_family($paper->feedbacklanguagefont ?? 'freesans');
            $feedbackhtml = html_writer::tag('div', 'feedback: ' . htmlspecialchars($feedback), [
                'style' => 'position: absolute; bottom: 4px; left: 4px; right: 4px; font-family: ' . $feedbackfontcss . '; font-size: 0.7em; font-weight: normal; color: #666; background: rgba(255,255,255,0.8); line-height: 1.2; max-height: 30%; overflow: hidden;'
            ]);
        }
        
        // Determine display HTML
        if ($area->isnamefield == 3) {
            // Display Only: Show the original image snippet
            $snippeturl = moodle_url::make_pluginfile_url($context->id, 'mod_paper', 'responsesnippet', $itemid, '/', 'snippet.jpg');
            $displayhtml = html_writer::empty_tag('img', [
                'src' => $snippeturl,
                'style' => 'max-width: 100%; max-height: 100%; object-fit: contain;'
            ]);
        } else if ($corrected !== '' && $area->grammarcorrections !== 'no' && !$area->isnamefield) {
            $displayhtml = \mod_paper\utils::build_combined_diff($ocr, $corrected);
        } else {
            $displayhtml = htmlspecialchars($corrected !== '' ? $corrected : $ocr);
        }

        // Grade badge at top right of area
        $gradehtml = '';
        if ($grade !== null && !$area->isnamefield) {
            $gradehtml = html_writer::tag('div', (round($grade, 2) + 0), [
                'style' => 'position: absolute; top: -20px; right: -25px; font-weight: bold; font-size: 2em; color: green; z-index: 30; background: white; border: 1px solid green; padding: 2px 6px; border-radius: 4px;'
            ]);
        }
        
        $contenthtml = html_writer::tag('div', $displayhtml, ['style' => 'width: 100%;']);
        echo html_writer::tag('div', $contenthtml . ($feedbackhtml ?? '') . $gradehtml, [
            'style' => $style,
            'class' => 'eval-item-area',
            'id' => 'item_area_' . $area->id,
            'data-item-id' => $itemid,
            'data-area-id' => $area->id,
            'data-is-name-field' => $area->isnamefield,
            'data-ocr' => $ocr,
            'data-corrected' => $corrected,
            'data-feedback' => $feedback,
            'data-grade' => $grade,
            'data-responsenumber' => $area->responsenumber
        ]);
    }
    if (($paper->showtotalscore ?? 1)) {
        $scoredisplay = (round($currenteval->totalgrade ?? 0, 2) + 0) . ' / ' . $maxpossible;
        $scorestyle = sprintf(
            'position: absolute; left: 5%%; top: %s%%; font-size: 22px; font-weight: bold; color: #d9534f; background: rgba(255,255,255,0.9); padding: 5px 15px; border-radius: 8px; border: 2px solid #d9534f; z-index: 30;',
            min(92, $bottommost_y + 2) // Ensure it doesn't fall off the bottom
        );
        echo html_writer::tag('div', "Total score: <span id='total-grade-display'>" . $scoredisplay . "</span>", ['style' => $scorestyle]);
    }
    
    echo html_writer::end_tag('div'); // end template-container

    // Right side: Sidebar form
    echo html_writer::start_tag('div', [
        'id' => 'edit-sidebar',
        'style' => 'width: 350px; padding: 20px; border-left: 1px solid #ccc; background: #f9f9f9; display: none;'
    ]);
    echo html_writer::tag('h4', 'Edit Response <span id="sidebar-area-num"></span>', ['class' => 'mb-3']);
    echo html_writer::start_tag('form', ['id' => 'edit-item-form']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'itemid', 'id' => 'field-itemid']);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'areaid', 'id' => 'field-areaid']);
    
    echo html_writer::start_tag('div', ['class' => 'form-group', 'id' => 'group-grade']);
    echo html_writer::tag('label', 'Grade');
    echo html_writer::empty_tag('input', ['type' => 'number', 'step' => '0.5', 'name' => 'grade', 'id' => 'field-grade', 'class' => 'form-control']);
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class' => 'form-group']);
    echo html_writer::tag('label', 'Original Text (Read Only)');
    echo html_writer::tag('div', '', ['id' => 'field-ocr-readonly', 'class' => 'p-2 border rounded bg-white', 'style' => 'min-height: 50px; font-style: italic; color: #666;']);
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class' => 'form-group']);
    echo html_writer::tag('label', 'Corrected Text');
    echo html_writer::tag('textarea', '', ['name' => 'correctedtext', 'id' => 'field-correctedtext', 'class' => 'form-control', 'rows' => 4]);
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class' => 'form-group', 'id' => 'group-feedback']);
    echo html_writer::tag('label', 'Feedback');
    echo html_writer::tag('textarea', '', ['name' => 'feedback', 'id' => 'field-feedback', 'class' => 'form-control', 'rows' => 3]);
    echo html_writer::end_tag('div');

    echo html_writer::tag('button', 'Save Changes', ['type' => 'submit', 'class' => 'btn btn-primary btn-block', 'id' => 'btn-save-item']);
    echo html_writer::tag('button', 'Cancel', ['type' => 'button', 'class' => 'btn btn-link btn-block', 'id' => 'btn-cancel-edit']);
    
    echo html_writer::end_tag('form');
    echo html_writer::end_tag('div'); // end edit-sidebar
    
    echo html_writer::end_tag('div'); // end view-eval-page-wrapper

    // Initialize JS
    $PAGE->requires->js_call_amd('mod_paper/view_eval', 'init', [
        'cmid' => $cm->id,
        'evalid' => $evalid,
        'maxpossible' => $maxpossible
    ]);

} else {
    echo $OUTPUT->notification("Warning: Underlying worksheet template image not found. Please re-upload it on the Setup screen.");
}

$viewurl = new moodle_url('/mod/paper/view.php', ['id' => $cm->id]);
echo html_writer::link($viewurl, 'Return to Top', ['class' => 'btn btn-secondary mt-3 mb-3 d-block text-center']);

echo $OUTPUT->footer();
