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
 * OpenAI ChatBot Block - Standalone Interface
 *
 * This file provides a standalone interface for the OpenAI ChatBot
 * when accessed directly as a block plugin.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Your Institution
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/openai_chatbot/index.php'));
$PAGE->set_title('OpenAI ChatBot');
$PAGE->set_heading('OpenAI ChatBot');

// Check configuration
$apikey = get_config('block_openai_chatbot', 'apikey');
$assistantid = get_config('block_openai_chatbot', 'assistantid');

echo $OUTPUT->header();

if (empty($apikey) || empty($assistantid)) {
    echo $OUTPUT->notification('OpenAI API configuration is missing. Please configure the plugin in Site administration.', 'error');
    
    echo html_writer::tag('h3', 'Configuration Required');
    echo html_writer::tag('p', 'To use the OpenAI ChatBot, you need to configure:');
    echo html_writer::start_tag('ul');
    echo html_writer::tag('li', 'OpenAI API Key');
    echo html_writer::tag('li', 'OpenAI Assistant ID');
    echo html_writer::end_tag('ul');
    
    if (has_capability('moodle/site:config', $context)) {
        echo html_writer::tag('p', 
            html_writer::link(new moodle_url('/admin/settings.php?section=blocksettingopenai_chatbot'), 
            'Configure ChatBot Settings', ['class' => 'btn btn-primary'])
        );
    }
} else {
    echo $OUTPUT->notification('OpenAI ChatBot is configured and ready to use!', 'success');
    
    echo html_writer::tag('div', 
        'Add the ChatBot block to your courses to start using it.',
        ['class' => 'alert alert-info']
    );
    
    echo html_writer::tag('h3', 'How to Use');
    echo html_writer::start_tag('ol');
    echo html_writer::tag('li', 'Go to any course where you have editing permissions');
    echo html_writer::tag('li', 'Turn editing on');
    echo html_writer::tag('li', 'Add a block and select "OpenAI ChatBot"');
    echo html_writer::tag('li', 'Students and teachers can now ask questions in that course');
    echo html_writer::end_tag('ol');
    
    if (has_capability('moodle/site:config', $context)) {
        echo html_writer::tag('p', 
            html_writer::link(new moodle_url('/admin/settings.php?section=blocksettingopenai_chatbot'), 
            'Manage ChatBot Settings', ['class' => 'btn btn-secondary'])
        );
    }
}

echo $OUTPUT->footer();