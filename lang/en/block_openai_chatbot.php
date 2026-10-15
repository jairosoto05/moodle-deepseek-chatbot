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
 * English language strings for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Your Institution
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin info
$string['pluginname'] = 'OpenAI ChatBot';
$string['openai_chatbot'] = 'OpenAI ChatBot';
$string['openai_chatbot:addinstance'] = 'Add a new OpenAI ChatBot block';
$string['openai_chatbot:myaddinstance'] = 'Add a new OpenAI ChatBot block to Dashboard';

// Block interface
$string['chatbot_title'] = 'AI Tutor';
$string['welcome_message'] = '💡 Ask a question to start chatting with the AI tutor';
$string['input_placeholder'] = 'Ask your question...';
$string['ask_button'] = 'Ask';
$string['response_header'] = 'Response:';
$string['error_prefix'] = 'Error:';

// Configuration
$string['config_missing'] = 'OpenAI API configuration is missing. Please contact your administrator.';

// Settings
$string['settings_general'] = 'General Settings';
$string['settings_general_desc'] = 'Configure the OpenAI ChatBot integration settings.';
$string['settings_bot_name'] = 'Bot Name';
$string['settings_bot_name_desc'] = 'Customize the name of your chatbot as it appears to users.';
$string['settings_apikey'] = 'OpenAI API Key';
$string['settings_apikey_desc'] = 'Enter your OpenAI API key. You can get one from https://platform.openai.com/';
$string['settings_assistantid'] = 'Assistant ID';
$string['settings_assistantid_desc'] = 'Enter the ID of your OpenAI Assistant. Create one at https://platform.openai.chat/assistants';
$string['settings_course_context'] = 'Enable Course Context';
$string['settings_course_context_desc'] = 'When enabled, the chatbot will receive information about the current course to provide more relevant responses.';
$string['settings_max_response_time'] = 'Maximum Response Time';
$string['settings_max_response_time_desc'] = 'Maximum time in seconds to wait for OpenAI response (default: 30)';

// JavaScript strings
$string['js_thinking'] = 'Thinking...';
$string['js_writing'] = 'Writing...';
$string['js_assistant_thinking'] = 'The assistant is thinking';
$string['js_error_occurred'] = 'An error occurred!';
$string['js_try_again'] = 'Try again';
$string['js_writing_question'] = 'Writing question';

// Privacy
$string['privacy:metadata'] = 'The OpenAI ChatBot block does not store any personal data locally. However, user questions are sent to OpenAI\'s servers for processing.';
$string['privacy:metadata:openai'] = 'User questions are sent to OpenAI for processing';
$string['privacy:metadata:openai:question'] = 'The question asked by the user';
$string['privacy:metadata:openai:course_context'] = 'Course information to provide context for responses';

// Additional strings
$string['no_config'] = 'OpenAI API configuration is missing. Please contact your administrator.';
$string['no_answer'] = '[No answer]';
$string['timeout_error'] = 'Execution did not finish in time. Status: {$a}';
$string['create_thread_error'] = 'Error creating thread. API response: {$a}';
$string['create_run_error'] = 'Error creating run. API response: {$a}';
$string['openai_api_error'] = 'OpenAI API Error: {$a}';
$string['network_error'] = 'Network error occurred. Please try again later.';

// Block capabilities
$string['openai_chatbot:view'] = 'View OpenAI ChatBot block';

// Error validation messages
$string['invalid_context'] = 'Invalid context';
$string['invalid_block_instance'] = 'Invalid block instance';
$string['question_empty'] = 'Question cannot be empty';
$string['error_message'] = 'Error: {$a}';
$string['strong_error'] = 'Error:';

// JavaScript fallback strings
$string['js_writing_fallback'] = 'Escribiendo...';
$string['js_thinking_fallback'] = 'Pensando...';
$string['js_assistant_thinking_fallback'] = 'El asistente está pensando';
$string['js_error_occurred_fallback'] = 'An error occurred';
$string['js_network_error'] = 'Network error occurred';
$string['js_ask_button_fallback'] = 'Preguntar';
$string['js_writing_question_fallback'] = 'Escribiendo pregunta';