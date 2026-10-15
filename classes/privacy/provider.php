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
 * Privacy provider for OpenAI ChatBot Block
 *
 * @package    block_openai_chatbot
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openai_chatbot\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\context;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider class for OpenAI ChatBot Block
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\data_provider {

    /**
     * Get metadata about data stored or transmitted by this plugin
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('openai', [
            'question' => 'privacy:metadata:openai:question',
            'course_context' => 'privacy:metadata:openai:course_context',
        ], 'privacy:metadata:openai');

        return $collection;
    }

    /**
     * Get contexts containing user data for the specified user
     *
     * @param int $userid
     * @return \core_privacy\local\request\contextlist
     */
    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        // This plugin does not store any user data locally
        return new \core_privacy\local\request\contextlist();
    }

    /**
     * Export user data for the specified user in the specified contexts
     *
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist) {
        // This plugin does not store any user data locally
    }

    /**
     * Delete user data for the specified user in the specified contexts
     *
     * @param \core_privacy\local\request\approved_contextlist $contextlist
     */
    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist) {
        // This plugin does not store any user data locally
    }

    /**
     * Delete user data for multiple users in the specified context
     *
     * @param \core_privacy\local\request\approved_userlist $userlist
     */
    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        // This plugin does not store any user data locally
    }

    /**
     * Get users in the specified context
     *
     * @param \core_privacy\local\request\userlist $userlist
     */
    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist) {
        // This plugin does not store any user data locally
    }

    /**
     * Delete all user data in the specified context
     *
     * @param context $context
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        // This plugin does not store any user data locally
    }
}