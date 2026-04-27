<?php
/**
 * Process Submissions Form for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_paper\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class process_submissions_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;
        
        $mform->addElement('header', 'general', get_string('submissions', 'mod_paper'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if (isset($this->_customdata['id'])) {
            $mform->setConstant('id', $this->_customdata['id']);
        }

        $filemanageropts = [
            'subdirs' => 0,
            'maxbytes' => 0, // Admin limit
            'maxfiles' => 50,
            'accepted_types' => ['.pdf', '.jpg', '.jpeg', '.png']
        ];
        
        $mform->addElement('filemanager', 'submissions_filemanager', get_string('submissions', 'mod_paper'), null, $filemanageropts);
        $mform->addRule('submissions_filemanager', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('processsubmissions', 'mod_paper'));
    }
}
