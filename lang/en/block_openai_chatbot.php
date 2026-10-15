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
//
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// but WITHOUT ANY WARRANTY; without even the implied warranty of
//
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for the ChatBot Block (DeepSeek provider).
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['api_error_auth'] = 'The DeepSeek API key is invalid. Please ask an administrator to check it in the ChatBot block settings.';
$string['api_error_balance'] = 'The DeepSeek account has no remaining balance. Please ask an administrator to top it up.';
$string['api_error_emptyresponse'] = 'DeepSeek returned an empty answer. Please try asking the question differently.';
$string['api_error_http'] = 'DeepSeek returned an unexpected HTTP error. Please try again later.';
$string['api_error_invalidjson'] = 'DeepSeek returned a response that could not be processed. Please try again later.';
$string['api_error_invalidrequest'] = 'DeepSeek rejected the request. Please ask an administrator to review the ChatBot block settings.';
$string['api_error_network'] = 'The DeepSeek API could not be reached. Please check the network connection and try again.';
$string['api_error_ratelimit'] = 'The DeepSeek API is receiving too many requests. Please wait a moment and try again.';
$string['api_error_server'] = 'The DeepSeek service is temporarily unavailable. Please try again in a few moments.';
$string['api_error_timeout'] = 'DeepSeek took too long to answer. Please try again, or ask an administrator to increase the maximum response time.';
$string['api_error_unexpected'] = 'An unexpected error occurred while contacting DeepSeek. Please try again later.';
$string['apikey_missing'] = 'The DeepSeek API key is not configured. Please ask an administrator to add it in the ChatBot block settings.';
$string['ask_button'] = 'Ask';
$string['chatbot_title'] = 'AI Tutor';
$string['config_missing'] = 'The DeepSeek API is not configured yet. Please contact your administrator.';
$string['course_context_description'] = 'Course description';
$string['course_context_due'] = 'due';
$string['course_context_general'] = 'General Moodle context.';
$string['course_context_indays'] = 'in {$a} days';
$string['course_context_instruction'] = 'Please provide responses specifically related to this course content. If the question is not related to this course, politely indicate that you can only help with course-related topics. When the learner asks about the course, use the course structure listed above and do not invent sections, activities or deadlines that are not listed; if something is not listed, say so.';
$string['course_context_name'] = 'Course context:';
$string['course_context_noactivities'] = '(no activities)';
$string['course_context_overdue'] = 'overdue';
$string['course_context_structure'] = 'Course structure (only what this user can see):';
$string['course_context_thisweek'] = 'this week';
$string['course_context_title'] = 'Course name';
$string['course_context_today'] = 'Current date';
$string['course_context_truncated'] = '(Course structure truncated to {$a->sections} sections and {$a->activities} activities.)';
$string['endpoint_label'] = 'Endpoint';
$string['error_admin_hint'] = '(See the ChatBot block settings; technical details are in the debugging log.)';
$string['error_message'] = 'Error: {$a}';
$string['error_prefix'] = 'Error:';
$string['input_placeholder'] = 'Ask your question...';
$string['invalid_block_instance'] = 'Invalid block instance';
$string['invalid_context'] = 'Invalid context';
$string['js_ask_button_fallback'] = 'Ask';
$string['js_assistant_thinking'] = 'The assistant is thinking';
$string['js_assistant_thinking_fallback'] = 'The assistant is thinking';
$string['js_error_occurred'] = 'An error occurred!';
$string['js_error_occurred_fallback'] = 'An error occurred';
$string['js_network_error'] = 'Network error occurred';
$string['js_thinking'] = 'Thinking...';
$string['js_thinking_fallback'] = 'Thinking...';
$string['js_try_again'] = 'Try again';
$string['js_writing'] = 'Writing...';
$string['js_writing_fallback'] = 'Writing...';
$string['js_writing_question'] = 'Writing question';
$string['js_writing_question_fallback'] = 'Writing question';
$string['model_deepseek_flash'] = 'deepseek-flash (fast, general purpose)';
$string['model_deepseek_v4_pro'] = 'deepseek-v4-pro (most capable)';
$string['openai_chatbot'] = 'ChatBot (DeepSeek)';
$string['openai_chatbot:addinstance'] = 'Add a new ChatBot block';
$string['openai_chatbot:myaddinstance'] = 'Add a new ChatBot block to Dashboard';
$string['openai_chatbot:view'] = 'View the ChatBot block';
$string['pluginname'] = 'ChatBot (DeepSeek)';

$string['privacy:metadata'] = 'The ChatBot block does not store any personal data locally. However, user questions are sent to DeepSeek\'s servers for processing.';
$string['privacy:metadata:deepseek'] = 'User questions are sent to DeepSeek for processing';
$string['privacy:metadata:deepseek:course_context'] = 'Course information to provide context for responses';
$string['privacy:metadata:deepseek:course_deadlines'] = 'The dates and deadlines of the course activities visible to the user';
$string['privacy:metadata:deepseek:course_structure'] = 'The course sections and the activities visible to the user';
$string['privacy:metadata:deepseek:question'] = 'The question asked by the user';
$string['question_empty'] = 'Question cannot be empty';
$string['response_header'] = 'Response:';
$string['response_success'] = 'Response generated successfully';
$string['settings_apikey'] = 'DeepSeek API key';
$string['settings_apikey_desc'] = 'Enter your DeepSeek API key. You can create one at https://platform.deepseek.com/. The key is stored in the Moodle configuration and is never displayed to users.';
$string['settings_behaviour'] = 'Assistant behaviour';
$string['settings_behaviour_desc'] = 'Instructions and context sent to the model with every question.';
$string['settings_bot_name'] = 'Bot name';
$string['settings_bot_name_desc'] = 'Customize the name of your chatbot as it appears to users.';
$string['settings_course_contents'] = 'Include the course structure';
$string['settings_course_contents_dates'] = 'Include dates and deadlines';
$string['settings_course_contents_dates_desc'] = 'Include the current date and the deadlines of the activities (assignment due dates, quiz closing dates, and so on).';
$string['settings_course_contents_desc'] = 'Send the course sections and the activities the user is allowed to see, so the assistant can answer questions about what to do in the course. It increases the number of input tokens of every request.';
$string['settings_course_contents_max_activities'] = 'Maximum activities';
$string['settings_course_contents_max_activities_desc'] = 'Maximum number of activities included in the request.';
$string['settings_course_contents_max_sections'] = 'Maximum sections';
$string['settings_course_contents_max_sections_desc'] = 'Maximum number of course sections included in the request.';
$string['settings_course_context'] = 'Enable course context';
$string['settings_course_context_desc'] = 'When enabled, the chatbot receives information about the current course to provide more relevant responses.';
$string['settings_coursecontext'] = 'Course context';
$string['settings_coursecontext_desc'] = 'Control how much of the current course is sent to the model with every question.';
$string['settings_deepseek'] = 'DeepSeek API';
$string['settings_deepseek_desc'] = 'Connection settings for the DeepSeek API. The plugin calls https://api.deepseek.com/chat/completions.';
$string['settings_effort_high'] = 'High (default)';
$string['settings_effort_low'] = 'Low';
$string['settings_effort_max'] = 'Maximum';
$string['settings_general'] = 'General settings';
$string['settings_general_desc'] = 'Configure how the ChatBot block is presented to users.';
$string['settings_max_response_time'] = 'Maximum response time';
$string['settings_max_response_time_desc'] = 'Maximum time in seconds to wait for a DeepSeek response (default: 30).';
$string['settings_max_tokens'] = 'Maximum output tokens';
$string['settings_max_tokens_desc'] = 'Upper limit for generated tokens (1 to 393216). Leave as 0 to use the DeepSeek default for the selected mode.';
$string['settings_model'] = 'Model';
$string['settings_model_desc'] = 'DeepSeek model used to answer questions.';
$string['settings_reasoningeffort'] = 'Reasoning effort';
$string['settings_reasoningeffort_desc'] = 'How much reasoning the model performs when thinking mode is enabled. Ignored when thinking mode is disabled.';
$string['settings_systemprompt'] = 'System prompt';
$string['settings_systemprompt_default'] = 'You are a helpful teaching assistant for a Moodle course. Answer clearly and concisely, and help learners understand the course material.';
$string['settings_systemprompt_desc'] = 'Instructions that define the assistant behaviour. They are sent as the system message of every request.';
$string['settings_temperature'] = 'Temperature';
$string['settings_temperature_desc'] = 'Sampling temperature between 0 and 2. Lower values make answers more focused and deterministic. It has no effect in thinking mode.';
$string['settings_thinking_disabled'] = 'Disabled (faster and cheaper)';
$string['settings_thinking_enabled'] = 'Enabled (deeper reasoning)';
$string['settings_thinkingmode'] = 'Thinking mode';
$string['settings_thinkingmode_desc'] = 'Enable DeepSeek thinking mode for deeper reasoning. Disabling it makes answers faster and cheaper.';
$string['status_add_block'] = 'Add the ChatBot block to any course to start using it.';
$string['status_config_required'] = 'Configuration required';
$string['status_config_required_desc'] = 'To use the ChatBot block, an administrator must configure:';
$string['status_configured'] = 'The ChatBot block is configured and ready to use.';
$string['status_how_to_use'] = 'How to use';
$string['status_manage_settings'] = 'Manage ChatBot settings';
$string['strong_error'] = 'Error:';
$string['welcome_message'] = '💡 Ask a question to start chatting with the AI tutor';
