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
 * OpenAI ChatBot Block for Moodle
 *
 * This block allows students and teachers to interact with an OpenAI Assistant
 * directly from within their Moodle courses, providing contextualized help
 * and educational support.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * OpenAI ChatBot Block class
 *
 * This block integrates OpenAI's Assistant API with Moodle to provide
 * intelligent tutoring and course-specific assistance.
 */
class block_openai_chatbot extends block_base {
    
    /**
     * Initialize the block
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_openai_chatbot');
    }
    
    /**
     * Get block content
     *
     * @return stdClass Block content object
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->text = $this->get_chatbot_html();
        $this->content->footer = '';
        
        return $this->content;
    }
    
    /**
     * Generate the chatbot HTML interface using templates
     *
     * @return string HTML content for the chatbot interface
     */
    private function get_chatbot_html() {
        global $OUTPUT, $PAGE;
        
        // Add CSS and modern JS module
        $PAGE->requires->css('/blocks/openai_chatbot/styles.css');
        $PAGE->requires->js_call_amd('block_openai_chatbot/chatbot', 'init');
        
        // Get bot name from configuration
        $bot_name = get_config('block_openai_chatbot', 'bot_name') ?: get_string('chatbot_title', 'block_openai_chatbot');
        
        // Create the renderable interface (no server-side processing)
        $interface = new \block_openai_chatbot\output\chatbot_interface(
            $this->instance->id,
            $bot_name
        );
        
        // Get the renderer and render the interface
        $renderer = $PAGE->get_renderer('block_openai_chatbot');
        return $renderer->render_chatbot_interface($interface);
    }
    
    /**
     * Process a user question using OpenAI Assistant API
     *
     * @param string $question The user's question
     * @return string HTML response content
     */
    private function process_question($question) {
        global $COURSE, $DB;
        
        // Get configuration from admin settings
        $apikey = get_config('block_openai_chatbot', 'apikey');
        $assistantid = get_config('block_openai_chatbot', 'assistantid');
        
        if (empty($apikey) || empty($assistantid)) {
            return '<div class="chatbot-error">' . get_string('config_missing', 'block_openai_chatbot') . '</div>';
        }
        
        $html = '';
        $html .= '<div class="chatbot-question">📝 ' . htmlspecialchars($question) . '</div>';
        
        try {
            // Get course context for enhanced responses
            $coursecontext = $this->get_course_context();
            $contextual_question = $coursecontext . "User question: " . $question;
            
            // Step 1: Create thread
            $thread_response = $this->create_openai_thread($apikey);
            if (!$thread_response['success']) {
                throw new Exception($thread_response['error']);
            }
            $threadId = $thread_response['thread_id'];
            
            // Step 2: Add message to thread
            $message_response = $this->add_message_to_thread($apikey, $threadId, $contextual_question);
            if (!$message_response['success']) {
                throw new Exception($message_response['error']);
            }
            
            // Step 3: Create and wait for run completion
            $run_response = $this->create_and_wait_for_run($apikey, $threadId, $assistantid);
            if (!$run_response['success']) {
                throw new Exception($run_response['error']);
            }
            
            // Step 4: Get assistant response
            $response = $this->get_assistant_response($apikey, $threadId);
            if (!$response['success']) {
                throw new Exception($response['error']);
            }
            
            $html .= '<div class="chatbot-answer">';
            $html .= '<div class="chatbot-answer-header">🤖 ' . get_string('response_header', 'block_openai_chatbot') . '</div>';
            $html .= '<div class="chatbot-answer-content">' . nl2br(htmlspecialchars($response['content'])) . '</div>';
            $html .= '</div>';
            
        } catch (Exception $e) {
            $html .= '<div class="chatbot-error">❌ ' . get_string('error_prefix', 'block_openai_chatbot') . ' ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        return $html;
    }
    
    /**
     * Get course context for enhanced AI responses
     *
     * @return string Course context information
     */
    private function get_course_context() {
        global $COURSE, $DB;
        
        if (!isset($COURSE) || $COURSE->id <= 1) {
            return "General Moodle context. ";
        }
        
        $context = "Course context:\n";
        $context .= "Course name: " . $COURSE->fullname . "\n";
        
        if (!empty($COURSE->summary)) {
            $context .= "Course description: " . strip_tags($COURSE->summary) . "\n";
        }
        
        $context .= "Please provide responses specifically related to this course content. ";
        $context .= "If the question is not related to this course, politely indicate that you can only help with course-related topics.\n\n";
        
        return $context;
    }
    
    /**
     * Create OpenAI thread
     *
     * @param string $apikey OpenAI API key
     * @return array Success status and thread ID or error message
     */
    private function create_openai_thread($apikey) {
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ]);
        
        $response = $curl->post('https://api.openai.com/v1/threads', json_encode(array()));
        $info = $curl->get_info();
        
        if ($info['http_code'] != 200) {
            return array('success' => false, 'error' => 'Failed to create conversation thread');
        }
        
        $thread = json_decode($response, true);
        return array('success' => true, 'thread_id' => $thread['id']);
    }
    
    /**
     * Add message to OpenAI thread
     *
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @param string $message Message content
     * @return array Success status or error message
     */
    private function add_message_to_thread($apikey, $threadId, $message) {
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ]);
        
        $data = json_encode(array(
            'role' => 'user',
            'content' => $message
        ));
        
        $response = $curl->post('https://api.openai.com/v1/threads/' . $threadId . '/messages', $data);
        $info = $curl->get_info();
        
        if ($info['http_code'] != 200) {
            return array('success' => false, 'error' => 'Failed to send message');
        }
        
        return array('success' => true);
    }
    
    /**
     * Create run and wait for completion
     *
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @param string $assistantId Assistant ID
     * @return array Success status or error message
     */
    private function create_and_wait_for_run($apikey, $threadId, $assistantId) {
        // Create run
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2'
        ]);
        
        $data = json_encode(array('assistant_id' => $assistantId));
        $response = $curl->post('https://api.openai.com/v1/threads/' . $threadId . '/runs', $data);
        $info = $curl->get_info();
        
        if ($info['http_code'] != 200) {
            return array('success' => false, 'error' => 'Failed to execute assistant');
        }
        
        $run = json_decode($response, true);
        $runId = $run['id'];
        
        // Wait for completion
        $maxAttempts = 30;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            sleep(1);
            $attempts++;
            
            $curl->setHeader([
                'Authorization: Bearer ' . $apikey,
                'OpenAI-Beta: assistants=v2'
            ]);
            
            $response = $curl->get('https://api.openai.com/v1/threads/' . $threadId . '/runs/' . $runId);
            $info = $curl->get_info();
            
            if ($info['http_code'] != 200) {
                return array('success' => false, 'error' => 'Failed to check run status');
            }
            
            $runStatus = json_decode($response, true);
            $status = $runStatus['status'];
            
            if ($status == 'completed') {
                return array('success' => true);
            } elseif ($status == 'failed' || $status == 'cancelled' || $status == 'expired') {
                return array('success' => false, 'error' => 'Assistant failed to process the question');
            }
        }
        
        return array('success' => false, 'error' => 'Assistant response timeout');
    }
    
    /**
     * Get assistant response from thread
     *
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @return array Success status and content or error message
     */
    private function get_assistant_response($apikey, $threadId) {
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'OpenAI-Beta: assistants=v2'
        ]);
        
        $response = $curl->get('https://api.openai.com/v1/threads/' . $threadId . '/messages');
        $info = $curl->get_info();
        
        if ($info['http_code'] != 200) {
            return array('success' => false, 'error' => 'Failed to get response');
        }
        
        $messages = json_decode($response, true);
        
        // Find assistant response
        foreach ($messages['data'] as $message) {
            if ($message['role'] == 'assistant') {
                $content = $message['content'][0];
                if ($content['type'] == 'text') {
                    return array('success' => true, 'content' => $content['text']['value']);
                }
            }
        }
        
        return array('success' => false, 'error' => 'No response found');
    }
    
    /**
     * Define where this block can be displayed
     *
     * @return array Supported formats
     */
    public function applicable_formats() {
        return array('course' => true, 'course-category' => false, 'site' => false);
    }
    
    /**
     * Allow multiple instances of this block
     *
     * @return bool Whether multiple instances are allowed
     */
    public function instance_allow_multiple() {
        return false;
    }
    
    /**
     * Block has global configuration
     *
     * @return bool Whether block has configuration
     */
    public function has_config() {
        return true;
    }
}