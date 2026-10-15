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
 * Upgrade script for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for the block
 *
 * @param int $oldversion The old version of the block
 * @return bool True on success
 */
function xmldb_block_openai_chatbot_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Future upgrade steps will be added here when needed.
    
    if ($oldversion < 2025100200) {
        // Example of future upgrade step:
        // Add any necessary database changes or configuration updates here.
        
        // Save point reached.
        upgrade_block_savepoint(true, 2025100200, 'openai_chatbot');
    }

    return true;
}