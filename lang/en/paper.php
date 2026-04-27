<?php
/**
 * Strings for component 'paper'.
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Paper';
$string['modulename_help'] = 'A Moodle activity plugin that gives corrections, feedback and optionally a score on student’s written assignments or worksheets.';
$string['modulenameplural'] = 'Papers';
$string['pluginname'] = 'Paper';
$string['pluginadministration'] = 'Paper administration';

$string['enablemoodleusername'] = 'Enable Moodle username';
$string['enablemoodleusername_help'] = 'If enabled you can specify one of the response areas as a Moodle username field. Then scores can be added to the gradebook and users can see their own results.';
$string['username'] = 'Moodle username';
$string['freetext'] = 'Free text';
$string['targetlanguage'] = 'Target language';
$string['targetlanguage_help'] = 'The language the student is writing in.';
$string['targetlanguagefont'] = 'Target language font';
$string['targetlanguagefont_help'] = 'The font used to display the student\'s writing on the PDF and web view.';
$string['feedbacklanguage'] = 'Native language';
$string['feedbacklanguage_help'] = 'The language feedback should be given in.';
$string['feedbacklanguagefont'] = 'Native language font';
$string['feedbacklanguagefont_help'] = 'The font used to display feedback on the PDF and web view.';

$string['font_freesans'] = 'FreeSans (Generic)';
$string['font_courier'] = 'Courier (Monospace)';
$string['font_helvetica'] = 'Helvetica (Sans-Serif)';
$string['font_times'] = 'Times (Serif)';
$string['font_kozminproregular'] = 'KozMinProRegular (Japanese)';
$string['font_stsongstdlight'] = 'STSongStdLight (Chinese Simplified)';
$string['font_msungstdlight'] = 'MSungStdLight (Chinese Traditional)';
$string['font_cid0kr'] = 'CID0KR (Korean)';

// Admin settings
$string['paper:addinstance'] = 'Add a new Paper activity';
$string['paper:view'] = 'View Paper activity';
$string['paper:manage'] = 'Manage Paper activity';
$string['ghostscriptpath'] = 'Ghostscript path';
$string['ghostscriptpath_desc'] = 'Path to the ghostscript executable (e.g. /usr/bin/gs)';
$string['openaicredentials'] = 'OpenAI API Key';
$string['openaicredentials_desc'] = 'Your OpenAI API Key for processing images and generating feedback.';
$string['defaulttargetlanguage'] = 'Default target language';
$string['defaultfeedbacklanguage'] = 'Default native language';
$string['defaulttargetlanguage_desc'] = 'The default language the student is writing in.';
$string['defaultfeedbacklanguage_desc'] = 'The default language feedback should be given in.';
$string['defaulttargetlanguagefont'] = 'Default target language font';
$string['defaulttargetlanguagefont_desc'] = 'The default font for the student text.';
$string['defaultfeedbacklanguagefont'] = 'Default native language font';
$string['defaultfeedbacklanguagefont_desc'] = 'The default font for the feedback text.';
$string['gradingpresets'] = 'Grading Presets';
$string['gradingprompt_name'] = 'Site Preset {$a} Name';
$string['gradingprompt_content'] = 'Site Preset {$a} Content';
$string['managepresets'] = 'Manage Grading Presets';
$string['addpreset'] = 'Add Preset';
$string['editpreset'] = 'Edit Preset';
$string['deletepreset'] = 'Delete Preset';
$string['presetname'] = 'Preset Name';
$string['presetcontent'] = 'Preset Content';
$string['nopresets'] = 'No personal presets found.';
$string['selectpreset'] = 'Select Preset';
$string['presetsaved'] = 'Preset saved.';
$string['presetdeleted'] = 'Preset deleted.';
$string['confirmdeletepreset'] = 'Are you sure you want to delete this preset?';

// View / Setup
$string['setup'] = 'Setup Paper';
$string['viewsetup'] = 'Go to Setup';
$string['uploadtemplate'] = 'Upload Template';
$string['uploadsubmissions'] = 'Upload and Evaluate Submissions';
$string['viewreports'] = 'View Evaluations';
$string['processsubmissions'] = 'Process Submissions';
$string['templateimage'] = 'Template Image (JPG/PDF)';
$string['submissions'] = 'Submissions (JPG/PDF)';

$string['en-us'] = 'English (US)';
$string['es-us'] = 'Spanish (US)';
$string['en-au'] = 'English (Aus.)';
$string['en-ph'] = 'English (Phil.)';
$string['en-gb'] = 'English (GB)';
$string['fr-ca'] = 'French (Can.)';
$string['fr-fr'] = 'French (FR)';
$string['it-it'] = 'Italian (IT)';
$string['pt-br'] = 'Portuguese (BR)';
$string['en-in'] = 'English (IN)';
$string['es-es'] = 'Spanish (ES)';
$string['fil-ph'] = 'Filipino';
$string['de-de'] = 'German (DE)';
$string['de-ch'] = 'German (CH)';
$string['de-at'] = 'German (AT)';
$string['da-dk'] = 'Danish (DK)';
$string['hi-in'] = 'Hindi';
$string['ko-kr'] = 'Korean';
$string['ar-ae'] = 'Arabic (Gulf)';
$string['ar-sa'] = 'Arabic (Modern Standard)';
$string['zh-cn'] = 'Chinese (Mandarin-Mainland)';
$string['nl-nl'] = 'Dutch (NL)';
$string['nl-be'] = 'Dutch (BE)';
$string['en-ie'] = 'English (Ireland)';
$string['en-wl'] = 'English (Wales)';
$string['en-ab'] = 'English (Scotland)';
$string['en-nz'] = 'English (New Zealand)';
$string['en-za'] = 'English (South Africa)';
$string['fa-ir'] = 'Persian';
$string['he-il'] = 'Hebrew';
$string['id-id'] = 'Indonesian';
$string['ja-jp'] = 'Japanese';
$string['ms-my'] = 'Malay';
$string['mi-nz'] = 'Maori';
$string['pt-pt'] = 'Portuguese (PT)';
$string['ru-ru'] = 'Russian';
$string['ta-in'] = 'Tamil';
$string['te-in'] = 'Telugu';
$string['tr-tr'] = 'Turkish';
$string['uk-ua'] = 'Ukranian';
$string['eu-es'] = 'Basque';
$string['fi-fi'] = 'Finnish';
$string['hu-hu'] = 'Hungarian';
$string['sv-se'] = 'Swedish';
$string['no-no'] = 'Norwegian';
$string['nb-no'] = 'Norwegian (Bokmål)';
$string['nn-no'] = 'Norwegian (Nynorsk)';
$string['pl-pl'] = 'Polish';
$string['ro-ro'] = 'Romanian';
$string['bg-bg'] = 'Bulgarian';
$string['cs-cz'] = 'Czech';
$string['el-gr'] = 'Greek';
$string['hr-hr'] = 'Croatian';
$string['lt-lt'] = 'Lithuanian';
$string['lv-lv'] = 'Latvian';
$string['sk-sk'] = 'Slovak';
$string['sl-si'] = 'Slovenian';
$string['so-so'] = 'Somali';
$string['ps-af'] = 'Pashto';
$string['is-is'] = 'Icelandic';
$string['mk-mk'] = 'Macedonian';
$string['sr-rs'] = 'Serbian';
$string['vi-vn'] = 'Vietnamese';
$string['fieldrole'] = 'Field Role';
$string['fieldrole_help'] = 'Designate the purpose of this response area. Non-standard roles will disable automatic AI grading and feedback for this specific area.';
$string['fieldrole_standard'] = 'Standard (Graded)';
$string['fieldrole_fullname'] = 'Full Name';
$string['fieldrole_username'] = 'Moodle Username';
$string['fieldrole_displayonly'] = 'Display Only (No Grading)';
$string['showtotalscore'] = 'Show total score';
$string['showtotalscore_help'] = 'If enabled, the total score will be displayed at the bottom of the evaluation on the PDF and web view.';

