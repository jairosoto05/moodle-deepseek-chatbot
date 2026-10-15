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
 * External API for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->dirroot . '/blocks/openai_chatbot/block_openai_chatbot.php');

/**
 * External service class for OpenAI ChatBot
 */
class block_openai_chatbot_external extends external_api {

    /**
     * Parameters for ask_question function
     * @return external_function_parameters
     */
    public static function ask_question_parameters() {
        return new external_function_parameters(
            array(
                'question' => new external_value(PARAM_TEXT, 'The user question'),
                'blockinstanceid' => new external_value(PARAM_INT, 'Block instance ID'),
                'contextid' => new external_value(PARAM_INT, 'Context ID')
            )
        );
    }

    /**
     * Ask a question to the OpenAI Assistant
     * @param string $question The user question
     * @param int $blockinstanceid Block instance ID
     * @param int $contextid Context ID
     * @return array Response data
     */
    public static function ask_question($question, $blockinstanceid, $contextid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::ask_question_parameters(), array(
            'question' => $question,
            'blockinstanceid' => $blockinstanceid,
            'contextid' => $contextid
        ));

        // Validate context.
        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);
        
        // Ensure we have a valid context.
        if (!$context) {
            throw new invalid_parameter_exception(get_string('invalid_context', 'block_openai_chatbot'));
        }

        // Check capabilities.
        require_capability('block/openai_chatbot:view', $context);

        // Validate block instance.
        $blockinstance = $DB->get_record('block_instances', array('id' => $params['blockinstanceid']), '*', MUST_EXIST);
        
        // Ensure the block instance exists and is of the correct type.
        if ($blockinstance->blockname !== 'openai_chatbot') {
            throw new invalid_parameter_exception(get_string('invalid_block_instance', 'block_openai_chatbot'));
        }
        
        // Clean the question.
        $cleanquestion = clean_param($params['question'], PARAM_TEXT);
        
        if (empty($cleanquestion)) {
            throw new invalid_parameter_exception(get_string('question_empty', 'block_openai_chatbot'));
        }

        // Get block configuration.
        $apikey = get_config('block_openai_chatbot', 'apikey');
        $assistantid = get_config('block_openai_chatbot', 'assistantid');

        if (empty($apikey) || empty($assistantid)) {
            return array(
                'success' => false,
                'message' => get_string('config_missing', 'block_openai_chatbot'),
                'html' => '<div class="alert alert-danger">' . get_string('config_missing', 'block_openai_chatbot') . '</div>'
            );
        }

        try {
            // Get course context for the question.
            $coursecontext = self::get_course_context($context);
            $contextual_question = $coursecontext . "User question: " . $cleanquestion;

            // Process the question using OpenAI.
            $result = self::process_openai_question($apikey, $assistantid, $contextual_question, $cleanquestion);

            return $result;

        } catch (Exception $e) {
            // Log the error for debugging
            error_log('OpenAI ChatBot Error: ' . $e->getMessage());
            
            return array(
                'success' => false,
                'message' => get_string('error_message', 'block_openai_chatbot', $e->getMessage()),
                'html' => '<div class="alert alert-danger"><strong>' . get_string('strong_error', 'block_openai_chatbot') . '</strong> ' . 
                         htmlspecialchars($e->getMessage()) . '</div>'
            );
        }
    }

    /**
     * Return description for ask_question function
     * @return external_single_structure
     */
    public static function ask_question_returns() {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
                'message' => new external_value(PARAM_TEXT, 'Response message', VALUE_OPTIONAL),
                'html' => new external_value(PARAM_RAW, 'HTML response content')
            )
        );
    }

    /**
     * Process question using OpenAI API
     * @param string $apikey OpenAI API key
     * @param string $assistantid Assistant ID
     * @param string $contextual_question Question with context
     * @param string $original_question Original user question
     * @return array Response array
     */
    private static function process_openai_question($apikey, $assistantid, $contextual_question, $original_question) {
        // Step 1: Create thread.
        $thread_response = self::create_openai_thread($apikey);
        if (!$thread_response['success']) {
            throw new Exception($thread_response['error']);
        }
        $threadId = $thread_response['thread_id'];

        // Step 2: Add message to thread.
        $message_response = self::add_message_to_thread($apikey, $threadId, $contextual_question);
        if (!$message_response['success']) {
            throw new Exception($message_response['error']);
        }

        // Step 3: Create and wait for run completion.
        $run_response = self::create_and_wait_for_run($apikey, $threadId, $assistantid);
        if (!$run_response['success']) {
            throw new Exception($run_response['error']);
        }

        // Step 4: Get assistant response.
        $response = self::get_assistant_response($apikey, $threadId);
        if (!$response['success']) {
            throw new Exception($response['error']);
        }

        // Format the response HTML (only the answer, question is handled by JavaScript).
        $html = '<div class="chatbot-answer">';
        $html .= '<div class="chatbot-answer-header">🤖 ' . get_string('response_header', 'block_openai_chatbot') . '</div>';
        $html .= '<div class="chatbot-answer-content">' . nl2br(htmlspecialchars($response['content'])) . '</div>';
        $html .= '</div>';

        return array(
            'success' => true,
            'message' => 'Response generated successfully',
            'html' => $html
        );
    }

    /**
     * Get course context information
     * @param context $context Current context
     * @return string Context information
     */
    private static function get_course_context($context) {
        global $COURSE;

        if ($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) {
            $courseid = $context->get_course_context()->instanceid;
            if ($courseid && $courseid != SITEID) {
                $course = get_course($courseid);
                $contextstr = "Course context:\n";
                $contextstr .= "Course name: " . $course->fullname . "\n";
                
                if (!empty($course->summary)) {
                    $contextstr .= "Course description: " . strip_tags($course->summary) . "\n";
                }
                
                $contextstr .= "Please provide responses specifically related to this course content. ";
                $contextstr .= "If the question is not related to this course, politely indicate that you can only help with course-related topics.\n\n";
                
                return $contextstr;
            }
        }

        return "General Moodle context. ";
    }

    /**
     * Create OpenAI thread
     * @param string $apikey OpenAI API key
     * @return array Success status and thread ID or error message
     */
    private static function create_openai_thread($apikey) {
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
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @param string $message Message content
     * @return array Success status or error message
     */
    private static function add_message_to_thread($apikey, $threadId, $message) {
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
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @param string $assistantId Assistant ID
     * @return array Success status or error message
     */
    private static function create_and_wait_for_run($apikey, $threadId, $assistantId) {
        // Create run.
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
            return array('success' => false, 'error' => 'Failed to execute assistant: HTTP ' . $info['http_code'] . ' - ' . $response);
        }
        
        $run = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'error' => 'Invalid JSON response when creating run');
        }
        
        if (!isset($run['id'])) {
            return array('success' => false, 'error' => 'Missing run ID in response');
        }
        
        $runId = $run['id'];
        
        // Wait for completion.
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
                return array('success' => false, 'error' => 'Failed to check run status: HTTP ' . $info['http_code']);
            }
            
            $runStatus = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return array('success' => false, 'error' => 'Invalid JSON in run status response');
            }
            
            if (!isset($runStatus['status'])) {
                return array('success' => false, 'error' => 'Missing status in run response');
            }
            
            $status = $runStatus['status'];
            
            if ($status == 'completed') {
                return array('success' => true);
            } else if ($status == 'failed') {
                $errorDetails = isset($runStatus['last_error']) ? $runStatus['last_error']['message'] : 'Unknown error';
                return array('success' => false, 'error' => 'Assistant failed: ' . $errorDetails);
            } else if ($status == 'cancelled' || $status == 'expired') {
                return array('success' => false, 'error' => 'Assistant run was ' . $status);
            }
        }
        
        return array('success' => false, 'error' => 'Assistant response timeout after ' . $maxAttempts . ' attempts');
    }

    /**
     * Get assistant response from thread
     * @param string $apikey OpenAI API key
     * @param string $threadId Thread ID
     * @return array Success status and content or error message
     */
    private static function get_assistant_response($apikey, $threadId) {
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $apikey,
            'OpenAI-Beta: assistants=v2'
        ]);
        
        $response = $curl->get('https://api.openai.com/v1/threads/' . $threadId . '/messages');
        $info = $curl->get_info();
        
        if ($info['http_code'] != 200) {
            return array('success' => false, 'error' => 'Failed to get response: HTTP ' . $info['http_code']);
        }
        
        $messages = json_decode($response, true);
        
        // Check if JSON decode was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'error' => 'Invalid JSON response from OpenAI');
        }
        
        // Check if messages data exists
        if (!isset($messages['data']) || !is_array($messages['data'])) {
            return array('success' => false, 'error' => 'Invalid message format from OpenAI');
        }
        
        // Find assistant response
        foreach ($messages['data'] as $message) {
            if (isset($message['role']) && $message['role'] == 'assistant') {
                if (isset($message['content']) && is_array($message['content']) && count($message['content']) > 0) {
                    $content = $message['content'][0];
                    if (isset($content['type']) && $content['type'] == 'text') {
                        if (isset($content['text']['value'])) {
                            return array('success' => true, 'content' => $content['text']['value']);
                        }
                    }
                }
            }
        }
        
        return array('success' => false, 'error' => 'No assistant response found in messages');
    }
}