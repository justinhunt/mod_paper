<?php
/**
 * Admin settings for mod_paper
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configexecutable('mod_paper/ghostscriptpath',
        get_string('ghostscriptpath', 'mod_paper'),
        get_string('ghostscriptpath_desc', 'mod_paper'),
        '/usr/bin/gs'));

    $langoptions = \mod_paper\utils::get_lang_options();

    $settings->add(new admin_setting_configselect('mod_paper/defaulttargetlanguage',
        get_string('defaulttargetlanguage', 'mod_paper'),
        get_string('defaulttargetlanguage_desc', 'mod_paper'),
        \mod_paper\constants::M_LANG_ENUS, $langoptions));

    $settings->add(new admin_setting_configselect('mod_paper/defaultfeedbacklanguage',
        get_string('defaultfeedbacklanguage', 'mod_paper'),
        get_string('defaultfeedbacklanguage_desc', 'mod_paper'),
        \mod_paper\constants::M_LANG_ENUS, $langoptions));

    $fontoptions = \mod_paper\utils::get_font_options();

    $settings->add(new admin_setting_configselect('mod_paper/defaulttargetlanguagefont',
        get_string('defaulttargetlanguagefont', 'mod_paper'),
        get_string('defaulttargetlanguagefont_desc', 'mod_paper'),
        'courier', $fontoptions));

    $settings->add(new admin_setting_configselect('mod_paper/defaultfeedbacklanguagefont',
        get_string('defaultfeedbacklanguagefont', 'mod_paper'),
        get_string('defaultfeedbacklanguagefont_desc', 'mod_paper'),
        'freesans', $fontoptions));

    $settings->add(new admin_setting_configpasswordunmask('mod_paper/openaicredentials',
        get_string('openaicredentials', 'mod_paper'),
        get_string('openaicredentials_desc', 'mod_paper'),
        ''));

    $settings->add(new admin_setting_heading('mod_paper/gradingpresets_heading',
        get_string('gradingpresets', 'mod_paper'), ''));

    $settings->add(new admin_setting_configtext('mod_paper/gradingprompt_1_name',
        get_string('gradingprompt_name', 'mod_paper', 1),
        '', ''));
    $settings->add(new admin_setting_configtextarea('mod_paper/gradingprompt_1_content',
        get_string('gradingprompt_content', 'mod_paper', 1),
        '', ''));

    $settings->add(new admin_setting_configtext('mod_paper/gradingprompt_2_name',
        get_string('gradingprompt_name', 'mod_paper', 2),
        '', ''));
    $settings->add(new admin_setting_configtextarea('mod_paper/gradingprompt_2_content',
        get_string('gradingprompt_content', 'mod_paper', 2),
        '', ''));
}
