<?php
/**
 * Setup Script for mod_paper
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
require_capability('mod/paper:manage', $context = context_module::instance($cm->id));

$PAGE->set_url('/mod/paper/setup.php', array('id' => $cm->id));
$PAGE->set_title(format_string($paper->name));
$PAGE->set_heading(format_string($course->fullname));

// Handle form submission to save response areas
if ($_POST && isset($_POST['sesskey']) && confirm_sesskey() && empty($_FILES['templateimage'])) {
    $areas = $DB->get_records('paper_response_areas', ['paperid' => $paper->id]);
    $nft = optional_param_array('namefieldtype', [], PARAM_INT);
    
    $questions = optional_param_array('question', [], PARAM_RAW);
    $correctanswers = optional_param_array('correctanswer', [], PARAM_RAW);
    $cam = optional_param_array('correctanswermode', [], PARAM_ALPHANUMEXT);
    $gc = optional_param_array('grammarcorrections', [], PARAM_ALPHANUMEXT);
    $fm = optional_param_array('feedbackmode', [], PARAM_ALPHANUMEXT);
    $mgrade = optional_param_array('maxgrade', [], PARAM_FLOAT);
    $gi = optional_param_array('gradeinstructions', [], PARAM_RAW);
    
    $bx = optional_param_array('box_x', [], PARAM_FLOAT);
    $by = optional_param_array('box_y', [], PARAM_FLOAT);
    $bw = optional_param_array('box_w', [], PARAM_FLOAT);
    $bh = optional_param_array('box_h', [], PARAM_FLOAT);
    
    $submitted_ids = array_keys($questions);
    
    // 1. Delete removed areas
    foreach ($areas as $area) {
        if (!in_array($area->id, $submitted_ids)) {
            $DB->delete_records('paper_response_areas', ['id' => $area->id]);
        }
    }
    
    // 2. Update existing and Insert new
    $responsenumber = 1;
    foreach ($questions as $post_id => $question) {
        $record = new stdClass();
        $record->paperid = $paper->id;
        $record->responsenumber = $responsenumber++;
        
        $type = $nft[$post_id] ?? 0;
        if ($type == 1) {
            $record->isnamefield = 1;
        } elseif ($type == 2) {
            $record->isnamefield = 2;
        } elseif ($type == 3) {
            $record->isnamefield = 3;
        } else {
            $record->isnamefield = 0;
        }
        $record->question = $question;
        $record->correctanswer = $correctanswers[$post_id] ?? '';
        $record->correctanswermode = $cam[$post_id] ?? 'none';
        $record->grammarcorrections = $gc[$post_id] ?? 'no';
        $record->feedbackmode = $fm[$post_id] ?? 'none';
        $record->maxgrade = $mgrade[$post_id] ?? 0;
        $record->gradeinstructions = $gi[$post_id] ?? '';
        $record->box_x = $bx[$post_id] ?? 0;
        $record->box_y = $by[$post_id] ?? 0;
        $record->box_w = $bw[$post_id] ?? 0;
        $record->box_h = $bh[$post_id] ?? 0;
        
        // If the ID contains 'new', it's a dynamically added box
        if (strpos((string)$post_id, 'new') !== false) {
            $DB->insert_record('paper_response_areas', $record);
        } else {
            $record->id = $post_id;
            $DB->update_record('paper_response_areas', $record);
        }
    }
    
    \core\notification::success("Area configurations saved successfully.");
    redirect(new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]));
}

// Handle 'reset' param to upload a new template
if (optional_param('reset', 0, PARAM_INT)) {
    $DB->delete_records('paper_response_areas', ['paperid' => $paper->id]);
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_paper', 'template', 0);
    redirect(new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]));
}

// Check if an image is uploaded for setup
if (isset($_FILES['templateimage']) && $_FILES['templateimage']['error'] === UPLOAD_ERR_OK) {
    $tmpname = $_FILES['templateimage']['tmp_name'];
    $filename = $_FILES['templateimage']['name'];
    
    // Check if it's a PDF
    $mime = mime_content_type($tmpname);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if ($mime === 'application/pdf' || $ext === 'pdf') {
        $jpgpath = $tmpname . '.jpg';
        $cmd = sprintf(
            "gs -q -dSAFER -dBATCH -dNOPAUSE -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -dUseCropBox -dPDFFitPage -sPAPERSIZE=a4 -sOutputFile=%s %s",
            escapeshellarg($jpgpath),
            escapeshellarg($tmpname)
        );
        exec($cmd, $output, $returnvar);
        
        if ($returnvar === 0 && file_exists($jpgpath)) {
            $imagecontent = file_get_contents($jpgpath);
            unlink($jpgpath);
        } else {
            \core\notification::error("Failed to convert PDF to image for analysis.");
            redirect(new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]));
            exit;
        }
    } else {
        // Process the image directly
        $imagecontent = file_get_contents($tmpname);
    }
    
    $base64image = base64_encode($imagecontent);
    
    // Save image to Moodle File API
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_paper', 'template', 0);
    $filerecord = [
        'contextid' => $context->id,
        'component' => 'mod_paper',
        'filearea'  => 'template',
        'itemid'    => 0,
        'filepath'  => '/',
        'filename'  => 'template.jpg'
    ];
    $fs->create_file_from_string($filerecord, $imagecontent);
    
    // Call AI to identify areas
    $aimanager = new \mod_paper\ai_manager();
    try {
        $areas = $aimanager->identify_response_areas($base64image);
        // Save areas to DB... (Simplified for now)
        $DB->delete_records('paper_response_areas', ['paperid' => $paper->id]);
        foreach ($areas as $index => $area) {
            $record = new stdClass();
            $record->paperid = $paper->id;
            $record->responsenumber = $index + 1;
            $record->isnamefield = ($index === 0) ? 1 : 0; // default first one
            
            if (isset($area->xmin)) {
                $record->box_x = $area->xmin / 10;
                $record->box_y = $area->ymin / 10;
                $record->box_w = ($area->xmax - $area->xmin) / 10;
                $record->box_h = ($area->ymax - $area->ymin) / 10;
            } else {
                $record->box_x = $area->x ?? 0;
                $record->box_y = $area->y ?? 0;
                $record->box_w = $area->w ?? 0;
                $record->box_h = $area->h ?? 0;
            }
            $DB->insert_record('paper_response_areas', $record);
        }
        redirect(new moodle_url('/mod/paper/setup.php', ['id' => $cm->id]));
    } catch (\Exception $e) {
        \core\notification::error("Error analyzing template: " . $e->getMessage());
    }
}

// Build Context for template
$contextdata = [
    'courseid' => $course->id,
    'cmid' => $cm->id,
    'actionurl' => new moodle_url('/mod/paper/setup.php', ['id' => $cm->id])->out(false),
    'sesskey' => sesskey(),
    'enablemoodleusername' => in_array($paper->namefieldrole, ['1', 1, 'username'], true),
    'preset_options' => \mod_paper\utils::get_grading_preset_options_list(),
    'preset_contents_json' => \mod_paper\utils::get_grading_presets_json(),
    'manage_presets_url' => new moodle_url('/mod/paper/presets.php', ['id' => $cm->id])->out(false),
    'responseareas' => []
];

// Load image base64 if exists
$fs = get_file_storage();
$file = $fs->get_file($context->id, 'mod_paper', 'template', 0, '/', 'template.jpg');
if ($file) {
    $contextdata['imagebase64'] = base64_encode($file->get_content());
}

$areas = $DB->get_records('paper_response_areas', ['paperid' => $paper->id], 'responsenumber ASC');
if (!empty($areas)) {
    $contextdata['hastemplate'] = true;
    foreach ($areas as $area) {
        $contextdata['responseareas'][] = [
            'id' => $area->id,
            'responsenumber' => $area->responsenumber,
            'box_x' => $area->box_x,
            'box_y' => $area->box_y,
            'box_w' => $area->box_w,
            'box_h' => $area->box_h,
            'isnamefield' => $area->isnamefield,
            'namefield_none' => ($area->isnamefield == 0),
            'namefield_fullname' => ($area->isnamefield == 1),
            'namefield_username' => ($area->isnamefield == 2),
            'namefield_displayonly' => ($area->isnamefield == 3),
            'question' => $area->question,
            'correctanswer' => $area->correctanswer,
            'correctanswermode' => $area->correctanswermode,
            'correctanswermode_none' => ($area->correctanswermode === 'none'),
            'correctanswermode_exactly' => ($area->correctanswermode === 'exactly'),
            'correctanswermode_relevant' => ($area->correctanswermode === 'relevant'),
            'correctanswermode_samemeaning' => ($area->correctanswermode === 'samemeaning'),
            'grammarcorrections' => $area->grammarcorrections,
            'grammarcorrections_no' => ($area->grammarcorrections === 'no'),
            'grammarcorrections_major' => ($area->grammarcorrections === 'major'),
            'grammarcorrections_all' => ($area->grammarcorrections === 'all'),
            'feedbackmode' => $area->feedbackmode ?? 'none',
            'feedbackmode_none' => (($area->feedbackmode ?? 'none') === 'none'),
            'feedbackmode_grammar' => (($area->feedbackmode ?? 'none') === 'grammar'),
            'feedbackmode_incorrect' => (($area->feedbackmode ?? 'none') === 'incorrect'),
            'feedbackoverall' => (($area->feedbackmode ?? 'none') === 'overall'), // Compatibility if needed
            'feedbackmode_overall' => (($area->feedbackmode ?? 'none') === 'overall'),
            'maxgrade' => ($area->maxgrade !== null) ? round($area->maxgrade, 2) + 0 : 0,
            'gradeinstructions' => $area->gradeinstructions,
        ];
    }
    
    // Pass json array for Javascript box drawing
    $contextdata['responseareas_json'] = json_encode($contextdata['responseareas']);
}

echo $OUTPUT->header();
echo $OUTPUT->heading("Setup Template for: " . format_string($paper->name));

echo $OUTPUT->render_from_template('mod_paper/setup', $contextdata);

echo $OUTPUT->footer();
