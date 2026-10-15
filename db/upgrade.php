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
 * Upgrade script for the ChatBot Block.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function for the block.
 *
 * @param int $oldversion The old version of the block
 * @return bool True on success
 */
function xmldb_block_openai_chatbot_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2025101600) {
        // The plugin moved from the OpenAI Assistant API to the DeepSeek chat completions API.
        // The web service contract (function name and its parameters) is unchanged, so existing
        // tokens keep working. Only the stored credentials change:
        //
        // The legacy "apikey" and "assistantid" settings belong to OpenAI and cannot be reused
        // with DeepSeek. They are removed so that the plugin never sends an OpenAI credential to
        // the DeepSeek endpoint. The administrator has to enter a DeepSeek API key once before
        // the block works again.
        //
        // Note: settings that keep working unchanged (bot_name, course_context, max_response_time)
        // are intentionally left untouched.
        $legacy = ['apikey', 'assistantid'];
        foreach ($legacy as $name) {
            if (get_config('block_openai_chatbot', $name) !== false) {
                unset_config($name, 'block_openai_chatbot');
            }
        }

        // The external service was published as "OpenAI ChatBot Service". Rename the existing
        // record before Moodle synchronises db/services.php, otherwise the service and any access
        // token issued for it would be deleted and recreated (losing the token).
        $legacyservice = $DB->get_record('external_services', [
            'component' => 'block_openai_chatbot',
            'name' => 'OpenAI ChatBot Service',
        ]);
        if ($legacyservice) {
            $DB->set_field('external_services', 'name', 'ChatBot Service', ['id' => $legacyservice->id]);
        }

        // Enable the new defaults once, without overwriting values an administrator already set.
        $defaults = [
            'model' => 'deepseek-flash',
            'thinkingmode' => 'enabled',
            'reasoningeffort' => 'high',
            'temperature' => 1,
            'max_tokens' => 0,
        ];
        foreach ($defaults as $name => $value) {
            if (get_config('block_openai_chatbot', $name) === false) {
                set_config($name, $value, 'block_openai_chatbot');
            }
        }

        // Save point reached.
        upgrade_block_savepoint(true, 2025101600, 'openai_chatbot');
    }

    if ($oldversion < 2025101602) {
        // The course context can now include the course structure (sections, visible activities and
        // their deadlines). The new settings are checkboxes and integers with sensible defaults, so
        // Moodle applies them automatically; this save point only records the change.
        upgrade_block_savepoint(true, 2025101602, 'openai_chatbot');
    }

    if ($oldversion < 2025101601) {
        // The block settings page lives under Site administration > Plugins > Blocks. No schema
        // change is needed for this step; the savepoint simply records the migration ordering for
        // installations that upgraded from the OpenAI based release.
        upgrade_block_savepoint(true, 2025101601, 'openai_chatbot');
    }

    return true;
}
