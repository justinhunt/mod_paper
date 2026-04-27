<?php
/**
 * Preset form for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_paper\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class preset_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'presetid');
        $mform->setType('presetid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('presetname', 'mod_paper'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'content', get_string('presetcontent', 'mod_paper'), ['rows' => 10, 'cols' => 60]);
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        $this->add_action_buttons();
    }
}
