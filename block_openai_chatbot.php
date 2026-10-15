<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * ChatBot Block for Moodle, powered by DeepSeek.
 *
 * This block allows students and teachers to interact with a DeepSeek powered assistant
 * directly from within their Moodle courses, providing contextualized help and
 * educational support.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_openai_chatbot\api\deepseek_client;
use block_openai_chatbot\local\course_context;

/**
 * ChatBot Block class.
 *
 * The interactive flow is rendered client side and served through the
 * block_openai_chatbot_ask_question web service. The server side rendering helpers below are
 * kept so that the block keeps working when JavaScript is unavailable.
 */
class block_openai_chatbot extends block_base {
    /**
     * Initialize the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_openai_chatbot');
    }

    /**
     * Get block content.
     *
     * @return stdClass Block content object
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = $this->get_chatbot_html();
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Generate the chatbot HTML interface using templates.
     *
     * @return string HTML content for the chatbot interface
     */
    private function get_chatbot_html() {
        // Add CSS and modern JS module.
        $this->page->requires->css('/blocks/openai_chatbot/styles.css');
        $this->page->requires->js_call_amd('block_openai_chatbot/chatbot', 'init');

        // Get bot name from configuration.
        $botname = get_config('block_openai_chatbot', 'bot_name') ?: get_string('chatbot_title', 'block_openai_chatbot');

        // Create the renderable interface (no server-side processing). The page context is
        // passed explicitly so that the interface does not rely on the global $PAGE.
        $interface = new \block_openai_chatbot\output\chatbot_interface(
            $this->instance->id,
            $botname,
            $this->page->context->id
        );

        // Get the renderer and render the interface.
        $renderer = $this->page->get_renderer('block_openai_chatbot');

        return $renderer->render_chatbot_interface($interface);
    }

    /**
     * Process a user question using the DeepSeek API.
     *
     * This is the no-JavaScript fallback: the AJAX web service is the primary path. It uses the
     * same client and the same course context as the web service.
     *
     * @param string $question The user's question
     * @return string HTML response content
     */
    private function process_question($question) {
        $html = '<div class="chatbot-question">📝 ' . s($question) . '</div>';

        $client = new deepseek_client();

        if (!$client->is_configured()) {
            return $html . '<div class="chatbot-error">'
                . get_string('config_missing', 'block_openai_chatbot') . '</div>';
        }

        try {
            $systemprompt = (string)(get_config('block_openai_chatbot', 'systemprompt') ?: '');
            $coursecontext = course_context::build($this->context);

            $messages = deepseek_client::build_messages($systemprompt, $question, $coursecontext);
            $response = $client->create_chat_completion($messages);

            $html .= '<div class="chatbot-answer">';
            $html .= '<div class="chatbot-answer-header">🤖 '
                . get_string('response_header', 'block_openai_chatbot') . '</div>';
            $html .= '<div class="chatbot-answer-content">' . nl2br(s($response['content'])) . '</div>';
            $html .= '</div>';
        } catch (moodle_exception $e) {
            debugging('ChatBot block DeepSeek error: ' . $e->errorcode, DEBUG_DEVELOPER);
            $html .= '<div class="chatbot-error">'
                . get_string('error_prefix', 'block_openai_chatbot') . ' '
                . s(get_string($e->errorcode, 'block_openai_chatbot')) . '</div>';
        }

        return $html;
    }

    /**
     * Define where this block can be displayed.
     *
     * @return array Supported formats
     */
    public function applicable_formats() {
        return ['course' => true, 'course-category' => false, 'site' => false];
    }

    /**
     * Allow multiple instances of this block.
     *
     * @return bool Whether multiple instances are allowed
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Block has global configuration.
     *
     * @return bool Whether block has configuration
     */
    public function has_config() {
        return true;
    }
}
