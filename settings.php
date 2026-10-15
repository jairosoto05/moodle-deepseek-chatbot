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
 * Settings for the ChatBot Block (DeepSeek provider).
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_openai_chatbot\api\deepseek_client;

// Moodle creates the block settings page and passes it in as $settings. Adding a second page
// with the same name would produce a "Duplicate admin page name" warning.
if ($hassiteconfig && $ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'block_openai_chatbot/general',
        get_string('settings_general', 'block_openai_chatbot'),
        get_string('settings_general_desc', 'block_openai_chatbot')
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/bot_name',
        get_string('settings_bot_name', 'block_openai_chatbot'),
        get_string('settings_bot_name_desc', 'block_openai_chatbot'),
        'AI Tutor',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_heading(
        'block_openai_chatbot/deepseek',
        get_string('settings_deepseek', 'block_openai_chatbot'),
        get_string('settings_deepseek_desc', 'block_openai_chatbot')
    ));

    // The API key is a secret: use the masked field and never expose it in the UI or logs.
    $settings->add(new admin_setting_configpasswordunmask(
        'block_openai_chatbot/deepseekapikey',
        get_string('settings_apikey', 'block_openai_chatbot'),
        get_string('settings_apikey_desc', 'block_openai_chatbot'),
        ''
    ));

    $modelchoices = [];
    foreach (deepseek_client::get_model_choices() as $model => $stringkey) {
        $modelchoices[$model] = get_string($stringkey, 'block_openai_chatbot');
    }

    $settings->add(new admin_setting_configselect(
        'block_openai_chatbot/model',
        get_string('settings_model', 'block_openai_chatbot'),
        get_string('settings_model_desc', 'block_openai_chatbot'),
        deepseek_client::DEFAULT_MODEL,
        $modelchoices
    ));

    $settings->add(new admin_setting_configselect(
        'block_openai_chatbot/thinkingmode',
        get_string('settings_thinkingmode', 'block_openai_chatbot'),
        get_string('settings_thinkingmode_desc', 'block_openai_chatbot'),
        deepseek_client::THINKING_ENABLED,
        [
            deepseek_client::THINKING_ENABLED => get_string('settings_thinking_enabled', 'block_openai_chatbot'),
            deepseek_client::THINKING_DISABLED => get_string('settings_thinking_disabled', 'block_openai_chatbot'),
        ]
    ));

    $settings->add(new admin_setting_configselect(
        'block_openai_chatbot/reasoningeffort',
        get_string('settings_reasoningeffort', 'block_openai_chatbot'),
        get_string('settings_reasoningeffort_desc', 'block_openai_chatbot'),
        deepseek_client::DEFAULT_REASONING_EFFORT,
        [
            'low' => get_string('settings_effort_low', 'block_openai_chatbot'),
            'high' => get_string('settings_effort_high', 'block_openai_chatbot'),
            'max' => get_string('settings_effort_max', 'block_openai_chatbot'),
        ]
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/temperature',
        get_string('settings_temperature', 'block_openai_chatbot'),
        get_string('settings_temperature_desc', 'block_openai_chatbot'),
        deepseek_client::DEFAULT_TEMPERATURE,
        PARAM_FLOAT
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/max_tokens',
        get_string('settings_max_tokens', 'block_openai_chatbot'),
        get_string('settings_max_tokens_desc', 'block_openai_chatbot'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_heading(
        'block_openai_chatbot/coursecontext',
        get_string('settings_coursecontext', 'block_openai_chatbot'),
        get_string('settings_coursecontext_desc', 'block_openai_chatbot')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_openai_chatbot/course_context',
        get_string('settings_course_context', 'block_openai_chatbot'),
        get_string('settings_course_context_desc', 'block_openai_chatbot'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_openai_chatbot/coursecontents',
        get_string('settings_course_contents', 'block_openai_chatbot'),
        get_string('settings_course_contents_desc', 'block_openai_chatbot'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/coursecontentsmaxsections',
        get_string('settings_course_contents_max_sections', 'block_openai_chatbot'),
        get_string('settings_course_contents_max_sections_desc', 'block_openai_chatbot'),
        \block_openai_chatbot\local\course_context::DEFAULT_MAX_SECTIONS,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/coursecontentsmaxactivities',
        get_string('settings_course_contents_max_activities', 'block_openai_chatbot'),
        get_string('settings_course_contents_max_activities_desc', 'block_openai_chatbot'),
        \block_openai_chatbot\local\course_context::DEFAULT_MAX_ACTIVITIES,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_openai_chatbot/coursecontentsdates',
        get_string('settings_course_contents_dates', 'block_openai_chatbot'),
        get_string('settings_course_contents_dates_desc', 'block_openai_chatbot'),
        1
    ));

    $settings->add(new admin_setting_heading(
        'block_openai_chatbot/behaviour',
        get_string('settings_behaviour', 'block_openai_chatbot'),
        get_string('settings_behaviour_desc', 'block_openai_chatbot')
    ));

    $settings->add(new admin_setting_configtextarea(
        'block_openai_chatbot/systemprompt',
        get_string('settings_systemprompt', 'block_openai_chatbot'),
        get_string('settings_systemprompt_desc', 'block_openai_chatbot'),
        get_string('settings_systemprompt_default', 'block_openai_chatbot'),
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/max_response_time',
        get_string('settings_max_response_time', 'block_openai_chatbot'),
        get_string('settings_max_response_time_desc', 'block_openai_chatbot'),
        30,
        PARAM_INT
    ));
}
