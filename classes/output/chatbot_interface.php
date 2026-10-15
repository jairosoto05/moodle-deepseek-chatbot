<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Chatbot interface renderable class for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openai_chatbot\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Renderable class for the chatbot interface
 */
class chatbot_interface implements renderable, templatable {
    
    /** @var int Block instance ID */
    protected $instanceid;
    
    /** @var string Bot name */
    protected $botname;
    
    /** @var string Response HTML content */
    protected $response;

    /**
     * Constructor
     *
     * @param int $instanceid Block instance ID
     * @param string $botname Bot name to display
     * @param string|null $response Optional response content (deprecated, kept for compatibility)
     */
    public function __construct($instanceid, $botname, $response = null) {
        $this->instanceid = $instanceid;
        $this->botname = $botname;
        $this->response = $response; // Not used in AJAX mode
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass Data ready for use in a mustache template
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;
        
        $data = new stdClass();
        $data->instanceid = $this->instanceid;
        $data->botname = $this->botname;
        $data->welcomemessage = get_string('welcome_message', 'block_openai_chatbot');
        $data->placeholdertext = get_string('input_placeholder', 'block_openai_chatbot');
        $data->buttontext = get_string('ask_button', 'block_openai_chatbot');
        $data->contextid = $PAGE->context->id;
        
        // No server-side response processing in AJAX mode
        
        return $data;
    }
}