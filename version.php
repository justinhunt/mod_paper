<?php
/**
 * Version details.
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024042706;
$plugin->requires  = 2021051700; // Moodle 3.11 for basic compat, we target 5.1+
$plugin->cron      = 0;
$plugin->component = 'mod_paper';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = 'v0.1';
$plugin->dependencies = [
];
