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
 * External API for the ChatBot block backed by DeepSeek.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Moodle 4.2 moved the external API classes into the core_external subsystem. Older versions only
// provide them, as global classes, from lib/externallib.php.
if (!class_exists('external_api')) {
    if (class_exists('core_external\\external_api')) {
        class_alias('core_external\\external_api', 'external_api');
        class_alias('core_external\\external_function_parameters', 'external_function_parameters');
        class_alias('core_external\\external_value', 'external_value');
        class_alias('core_external\\external_single_structure', 'external_single_structure');
        class_alias('core_external\\external_multiple_structure', 'external_multiple_structure');
    } else {
        require_once($CFG->libdir . '/externallib.php');
    }
}
require_once($CFG->libdir . '/blocklib.php');

use block_openai_chatbot\api\deepseek_client;
use block_openai_chatbot\local\course_context;

/**
 * External service class for the ChatBot block.
 *
 * The web service contract (function name, parameters and return structure) is unchanged
 * so that existing callers, including the mobile app, keep working.
 */
class block_openai_chatbot_external extends external_api {
    /**
     * Parameters for the ask_question function.
     *
     * @return external_function_parameters
     */
    public static function ask_question_parameters() {
        return new external_function_parameters(
            [
                'question' => new external_value(PARAM_TEXT, 'The user question'),
                'blockinstanceid' => new external_value(PARAM_INT, 'Block instance ID'),
                'contextid' => new external_value(PARAM_INT, 'Context ID'),
            ]
        );
    }

    /**
     * Ask a question to the DeepSeek powered assistant.
     *
     * @param string $question The user question.
     * @param int $blockinstanceid Block instance ID.
     * @param int $contextid Context ID.
     * @return array Response data.
     */
    public static function ask_question($question, $blockinstanceid, $contextid) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::ask_question_parameters(), [
            'question' => $question,
            'blockinstanceid' => $blockinstanceid,
            'contextid' => $contextid,
        ]);

        // Validate context.
        $context = context::instance_by_id($params['contextid']);
        self::validate_context($context);

        // Check capabilities.
        require_capability('block/openai_chatbot:view', $context);

        // Validate block instance.
        $blockinstance = $DB->get_record('block_instances', ['id' => $params['blockinstanceid']], '*', MUST_EXIST);

        // Ensure the block instance exists and is of the correct type.
        if ($blockinstance->blockname !== 'openai_chatbot') {
            throw new invalid_parameter_exception(get_string('invalid_block_instance', 'block_openai_chatbot'));
        }

        // Clean the question.
        $cleanquestion = clean_param($params['question'], PARAM_TEXT);

        if (trim($cleanquestion) === '') {
            throw new invalid_parameter_exception(get_string('question_empty', 'block_openai_chatbot'));
        }

        $client = new deepseek_client();

        if (!$client->is_configured()) {
            return [
                'success' => false,
                'message' => get_string('config_missing', 'block_openai_chatbot'),
                'html' => '<div class="chatbot-error">' . get_string('config_missing', 'block_openai_chatbot') . '</div>',
            ];
        }

        try {
            $result = self::process_deepseek_question($client, $cleanquestion, $context);

            return $result;
        } catch (moodle_exception $e) {
            // Log the error code only: the API key must never reach the logs.
            debugging('ChatBot block DeepSeek error: ' . $e->errorcode, DEBUG_DEVELOPER);

            return self::error_response($e->errorcode, $context);
        } catch (\Exception $e) {
            debugging('ChatBot block unexpected error: ' . get_class($e), DEBUG_DEVELOPER);

            return self::error_response('api_error_unexpected', $context);
        }
    }

    /**
     * Return description for the ask_question function.
     *
     * @return external_single_structure
     */
    public static function ask_question_returns() {
        return new external_single_structure(
            [
                'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
                'message' => new external_value(PARAM_TEXT, 'Response message', VALUE_OPTIONAL),
                'html' => new external_value(PARAM_RAW, 'HTML response content'),
            ]
        );
    }

    /**
     * Build a user facing error response.
     *
     * Administrators get the technical detail from the debugging log; regular users only see
     * a translated, generic message so that no provider or configuration detail leaks.
     *
     * @param string $errorcode Plugin language string identifier.
     * @param context $context Context used to decide how much detail is shown.
     * @return array Response data.
     */
    private static function error_response(string $errorcode, $context): array {
        $message = get_string($errorcode, 'block_openai_chatbot');

        // Administrators additionally get a pointer to the configuration page; the technical
        // detail stays in the debugging log and is never shown to regular users.
        if (has_capability('moodle/site:config', $context)) {
            $message .= ' ' . get_string('error_admin_hint', 'block_openai_chatbot');
        }

        return [
            'success' => false,
            'message' => $message,
            'html' => '<div class="chatbot-error">' . s($message) . '</div>',
        ];
    }

    /**
     * Process a question using the DeepSeek chat completions API.
     *
     * @param deepseek_client $client Configured client.
     * @param string $question Cleaned user question.
     * @param context $context Current context.
     * @return array Response array.
     * @throws moodle_exception When the API request or response fails.
     */
    private static function process_deepseek_question(deepseek_client $client, string $question, $context): array {
        $systemprompt = (string)(get_config('block_openai_chatbot', 'systemprompt') ?: '');
        $coursecontext = course_context::build($context);
        $messages = deepseek_client::build_messages($systemprompt, $question, $coursecontext);

        $response = $client->create_chat_completion($messages);

        $html = '<div class="chatbot-answer">';
        $html .= '<div class="chatbot-answer-header">🤖 ' . get_string('response_header', 'block_openai_chatbot') . '</div>';
        $html .= '<div class="chatbot-answer-content">' . nl2br(s($response['content'])) . '</div>';
        $html .= '</div>';

        return [
            'success' => true,
            'message' => get_string('response_success', 'block_openai_chatbot'),
            'html' => $html,
        ];
    }
}
