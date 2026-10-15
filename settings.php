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
 * Settings for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    
    $settings = new admin_settingpage('block_openai_chatbot', get_string('pluginname', 'block_openai_chatbot'));
    
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
    
    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/apikey',
        get_string('settings_apikey', 'block_openai_chatbot'),
        get_string('settings_apikey_desc', 'block_openai_chatbot'),
        '',
        PARAM_TEXT
    ));
    
    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/assistantid',
        get_string('settings_assistantid', 'block_openai_chatbot'),
        get_string('settings_assistantid_desc', 'block_openai_chatbot'),
        '',
        PARAM_TEXT
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'block_openai_chatbot/course_context',
        get_string('settings_course_context', 'block_openai_chatbot'),
        get_string('settings_course_context_desc', 'block_openai_chatbot'),
        1
    ));
    
    $settings->add(new admin_setting_configtext(
        'block_openai_chatbot/max_response_time',
        get_string('settings_max_response_time', 'block_openai_chatbot'),
        get_string('settings_max_response_time_desc', 'block_openai_chatbot'),
        30,
        PARAM_INT
    ));
    
    // Add to admin tree
    $ADMIN->add('blocksettings', $settings);
}