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
 * Privacy provider for the ChatBot Block.
 *
 * @package    block_openai_chatbot
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openai_chatbot\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider class for the ChatBot Block.
 *
 * The plugin stores no personal data locally: questions are only forwarded to DeepSeek for
 * processing and no conversation history is persisted in Moodle.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\data_provider {
    /**
     * Get metadata about data stored or transmitted by this plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('deepseek', [
            'question' => 'privacy:metadata:deepseek:question',
            'course_context' => 'privacy:metadata:deepseek:course_context',
            'course_structure' => 'privacy:metadata:deepseek:course_structure',
            'course_deadlines' => 'privacy:metadata:deepseek:course_deadlines',
        ], 'privacy:metadata:deepseek');

        return $collection;
    }
}
