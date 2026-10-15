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
 * ChatBot Block - standalone status page.
 *
 * This page reports whether the block is correctly configured. It is informational only and
 * never displays the configured API key.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

use block_openai_chatbot\api\deepseek_client;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/openai_chatbot/index.php'));

$pagetitle = get_string('pluginname', 'block_openai_chatbot');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$client = new deepseek_client();
$settings = $client->get_settings();
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'blocksettingopenai_chatbot']);
$canconfigure = has_capability('moodle/site:config', $context);

echo $OUTPUT->header();

if (!$client->is_configured()) {
    echo $OUTPUT->notification(get_string('config_missing', 'block_openai_chatbot'), 'error');

    echo html_writer::tag('h3', get_string('status_config_required', 'block_openai_chatbot'));
    echo html_writer::tag('p', get_string('status_config_required_desc', 'block_openai_chatbot'));
    echo html_writer::start_tag('ul');
    echo html_writer::tag('li', get_string('settings_apikey', 'block_openai_chatbot'));
    echo html_writer::tag('li', get_string('settings_model', 'block_openai_chatbot'));
    echo html_writer::end_tag('ul');
} else {
    echo $OUTPUT->notification(get_string('status_configured', 'block_openai_chatbot'), 'success');

    $modelchoices = deepseek_client::get_model_choices();
    $model = $settings['model'];
    $modellabel = isset($modelchoices[$model])
        ? get_string($modelchoices[$model], 'block_openai_chatbot')
        : $model;

    $list = html_writer::tag('li', get_string('settings_model', 'block_openai_chatbot') . ': ' . $modellabel);
    $list .= html_writer::tag('li', get_string('endpoint_label', 'block_openai_chatbot') . ': '
        . html_writer::tag('code', deepseek_client::get_endpoint()));
    echo html_writer::tag('ul', $list);

    echo html_writer::tag('h3', get_string('status_how_to_use', 'block_openai_chatbot'));
    echo html_writer::tag('p', get_string('status_add_block', 'block_openai_chatbot'));
}

if ($canconfigure) {
    echo html_writer::tag('p', html_writer::link(
        $settingsurl,
        get_string('status_manage_settings', 'block_openai_chatbot'),
        ['class' => 'btn btn-primary']
    ));
}

echo $OUTPUT->footer();
