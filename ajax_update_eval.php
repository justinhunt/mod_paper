<?php
/**
 * AJAX Handler for updating evaluation items in mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require('../../config.php');
require_once($CFG->dirroot.'/mod/paper/lib.php');

$itemid = optional_param('itemid', 0, PARAM_INT);
$areaid = optional_param('areaid', 0, PARAM_INT);
$evalid = optional_param('evalid', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$grade = optional_param('grade', null, PARAM_FLOAT);
$correctedtext = optional_param('correctedtext', '', PARAM_RAW);
$feedback = optional_param('feedback', '', PARAM_RAW);

header('Content-Type: application/json');

try {
    $debugparams = [
        'itemid' => $itemid,
        'areaid' => $areaid,
        'evalid' => $evalid,
        'cmid' => $cmid,
        'request_method' => $_SERVER['REQUEST_METHOD']
    ];

    if (!$cmid) {
        throw new Exception("Missing course module ID (cmid). Received: " . json_encode($debugparams));
    }
    if (!$evalid) {
        throw new Exception("Missing evaluation ID (evalid).");
    }
    if (!$areaid) {
        throw new Exception("Missing response area ID (areaid).");
    }

    $cm = get_coursemodule_from_id('paper', $cmid, 0, false);
    if (!$cm) {
        throw new Exception("Course module not found for ID: $cmid");
    }

    $context = context_module::instance($cm->id);
    require_login($cm->course, true, $cm);
    require_capability('mod/paper:manage', $context);

    $area = $DB->get_record('paper_response_areas', ['id' => $areaid]);
    if (!$area) {
        throw new Exception("Response area not found for ID: $areaid");
    }

    $paper = $DB->get_record('paper', ['id' => $cm->instance]);
    if (!$paper) {
        throw new Exception("Paper activity not found for instance: " . $cm->instance);
    }

    // Get or create item
    $item = null;
    if ($itemid > 0) {
        $item = $DB->get_record('paper_eval_items', ['id' => $itemid, 'evalid' => $evalid]);
        if (!$item) {
            // If item ID was provided but not found for this evaluation, maybe it's been deleted or ID is wrong
            // We'll try to find by area ID as a fallback
            $item = $DB->get_record('paper_eval_items', ['evalid' => $evalid, 'responseareaid' => $areaid]);
        }
    } else {
        $item = $DB->get_record('paper_eval_items', ['evalid' => $evalid, 'responseareaid' => $areaid]);
    }

    if (!$item) {
        $item = new stdClass();
        $item->evalid = $evalid;
        $item->responseareaid = $areaid;
        $item->ocrtext = ''; 
        $item->correctedtext = $correctedtext;
        $item->feedback = $feedback;
        $item->itemgrade = ($grade !== null && !$area->isnamefield) ? $grade : 0;
        $item->id = $DB->insert_record('paper_eval_items', $item);
    } else {
        $item->correctedtext = $correctedtext;
        $item->feedback = $feedback;
        if ($grade !== null && !$area->isnamefield) {
            $item->itemgrade = $grade;
        }
        $DB->update_record('paper_eval_items', $item);
    }

    // Special handling for name field - update the evaluation record too
    if ($area->isnamefield) {
        $DB->set_field('paper_evaluations', 'studentnametext', $correctedtext, ['id' => $evalid]);
    }

    // Recalculate total grade
    $sql = "SELECT SUM(pei.itemgrade) 
            FROM {paper_eval_items} pei
            JOIN {paper_response_areas} pra ON pra.id = pei.responseareaid
            WHERE pei.evalid = :evalid AND pra.isnamefield = 0";
    $totalgrade = $DB->get_field_sql($sql, ['evalid' => $evalid]);
    $totalgrade = round($totalgrade, 2) + 0;

    $DB->set_field('paper_evaluations', 'totalgrade', $totalgrade, ['id' => $evalid]);

    // Update Moodle gradebook if userid is set
    $evaluation = $DB->get_record('paper_evaluations', ['id' => $evalid]);
    if (!empty($evaluation->userid)) {
        $grades = [];
        $grades[$evaluation->userid] = new stdClass();
        $grades[$evaluation->userid]->userid = $evaluation->userid;
        $grades[$evaluation->userid]->rawgrade = $totalgrade;
        paper_grade_item_update($paper, $grades);
    }

    // Build new HTML for the area
    if ($item->correctedtext !== '' && $area->grammarcorrections !== 'no' && !$area->isnamefield) {
        $displayhtml = \mod_paper\utils::build_combined_diff($item->ocrtext, $item->correctedtext);
    } else {
        $displayhtml = htmlspecialchars($item->correctedtext !== '' ? $item->correctedtext : $item->ocrtext);
    }

    // Wrap the content in the same way view_eval.php does to maintain layout
    $wrappedhtml = html_writer::tag('div', $displayhtml, ['style' => 'width: 100%;']);
    
    // Add Grade badge (matching view_eval.php)
    if ($item->itemgrade !== null && !$area->isnamefield) {
        $gradehtml = html_writer::tag('div', (round($item->itemgrade, 2) + 0), [
            'style' => 'position: absolute; top: -20px; right: -25px; font-weight: bold; font-size: 2em; color: green; z-index: 30; background: white; border: 1px solid green; padding: 2px 6px; border-radius: 4px;'
        ]);
        $wrappedhtml .= $gradehtml;
    }

    if (!empty($item->feedback) && !$area->isnamefield) {
        $feedbackfontcss = \mod_paper\utils::get_css_font_family($paper->feedbacklanguagefont ?? 'freesans');
        $feedbackhtml = html_writer::tag('div', 'feedback: ' . htmlspecialchars($item->feedback), [
            'style' => 'position: absolute; bottom: 4px; left: 4px; right: 4px; font-family: ' . $feedbackfontcss . '; font-size: 0.7em; font-weight: normal; color: #666; background: rgba(255,255,255,0.8); line-height: 1.2; max-height: 30%; overflow: hidden;'
        ]);
        $wrappedhtml .= $feedbackhtml;
    }

    echo json_encode([
        'success' => true,
        'newhtml' => $wrappedhtml,
        'totalgrade' => $totalgrade,
        'itemid' => $item->id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
