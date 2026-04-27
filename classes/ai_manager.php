<?php
/**
 * AI Manager
 *
 * @package    mod_paper
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_paper;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/openai_handler.php');

class ai_manager {
    /**
     * @var ai_handler Base handler
     */
    protected $handler;

    public function __construct() {
        // Future proofing for multiple AI handlers.
        $this->handler = new openai_handler();
    }

    /**
     * Identify response areas in an image base64
     * We expect an array of bounding boxes: [{x, y, w, h}]
     */
    public function identify_response_areas($imagebase64) {
        return $this->handler->identify_response_areas($imagebase64);
    }

    /**
     * Extract handwritten text from a specific bounded area of an image
     */
    public function extract_text($imagebase64, $bbox) {
        return $this->handler->extract_text($imagebase64, $bbox);
    }

    /**
     * Evaluate a student's answer vs the required criteria
     */
    public function evaluate_response($studenttext, $criteria, $targetlang, $feedbacklang) {
        return $this->handler->evaluate_response($studenttext, $criteria, $targetlang, $feedbacklang);
    }

    /**
     * Batch process evaluations for a specific response area
     */
    public function batch_process_evaluations($area, $items, $feedbacklanguage = 'English') {
        return $this->handler->batch_process_evaluations($area, $items, $feedbacklanguage);
    }

    /**
     * Batch correct grammar for multiple texts
     */
    public function batch_correct_grammar($texts) {
        return $this->handler->batch_correct_grammar($texts);
    }
}
