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
 * External services definition for the ChatBot Block.
 *
 * The function name is part of the published contract (the block JavaScript and the mobile app
 * call it) and therefore keeps its original name.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_openai_chatbot_ask_question' => [
        'classname' => 'block_openai_chatbot_external',
        'methodname' => 'ask_question',
        'classpath' => 'blocks/openai_chatbot/classes/external.php',
        'description' => 'Submit a question to the DeepSeek powered assistant',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'block/openai_chatbot:view',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];

$services = [
    'ChatBot Service' => [
        'functions' => ['block_openai_chatbot_ask_question'],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
