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
 * Course context builder for the ChatBot Block.
 *
 * The model has no access to Moodle: it can only use what this plugin sends with every question.
 * This class builds that text out of the course the user is looking at, so that questions such as
 * "what do I have to do this week?" can be answered with real data.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openai_chatbot\local;

/**
 * Builds the course context sent to the model.
 *
 * Only information the requesting user is allowed to see is included: sections and activities are
 * filtered with {@see \cm_info::$uservisible}, which is the same criterion Moodle uses to decide
 * whether an activity is shown in the course page.
 */
class course_context {
    /** @var int Default maximum number of sections included in the prompt. */
    public const DEFAULT_MAX_SECTIONS = 10;

    /** @var int Default maximum number of activities included in the prompt. */
    public const DEFAULT_MAX_ACTIVITIES = 40;

    /** @var int How far back in time deadlines are looked up (seconds). */
    public const DEADLINE_WINDOW_PAST = 7 * DAYSECS;

    /** @var int How far ahead in time deadlines are looked up (seconds). */
    public const DEADLINE_WINDOW_FUTURE = 30 * DAYSECS;

    /** @var int Maximum length of an activity name included in the prompt. */
    public const MAX_NAME_LENGTH = 120;

    /** @var string Plugin component name. */
    private const COMPONENT = 'block_openai_chatbot';

    /**
     * Read the plugin settings with their defaults.
     *
     * @return array Options used by {@see self::build()} and {@see self::build_for_course()}.
     */
    public static function get_options(): array {
        return [
            'enabled' => self::get_bool_setting('course_context', true),
            'contents' => self::get_bool_setting('coursecontents', true),
            'dates' => self::get_bool_setting('coursecontentsdates', true),
            'max_sections' => self::get_int_setting('coursecontentsmaxsections', self::DEFAULT_MAX_SECTIONS),
            'max_activities' => self::get_int_setting('coursecontentsmaxactivities', self::DEFAULT_MAX_ACTIVITIES),
        ];
    }

    /**
     * Build the course context for a Moodle context.
     *
     * @param \context $context Context the block is being displayed in.
     * @param int|null $userid User the structure is built for, defaults to the current user.
     * @param array|null $options Options, defaults to the plugin settings.
     * @return string Context text, or an empty string when the feature is disabled.
     */
    public static function build(\context $context, ?int $userid = null, ?array $options = null): string {
        $options = $options ?? self::get_options();

        if (empty($options['enabled'])) {
            return '';
        }

        $coursecontext = $context->get_course_context(false);
        if (!$coursecontext || (int)$coursecontext->instanceid === SITEID) {
            return get_string('course_context_general', self::COMPONENT);
        }

        $course = get_course($coursecontext->instanceid);

        if ($userid === null) {
            global $USER;
            $userid = (int)$USER->id;
        }

        return self::build_for_course($course, $userid, $options);
    }

    /**
     * Build the course context for a course and a user.
     *
     * @param \stdClass $course Course record.
     * @param int $userid User the structure is built for.
     * @param array $options Options, missing keys fall back to the plugin settings.
     * @return string Context text.
     */
    public static function build_for_course(\stdClass $course, int $userid, array $options = []): string {
        $options = array_merge(self::get_options(), $options);
        $now = time();

        $lines = [];
        $lines[] = get_string('course_context_name', self::COMPONENT);
        $lines[] = get_string('course_context_title', self::COMPONENT) . ': ' . format_string($course->fullname);

        if (!empty($course->summary)) {
            $lines[] = get_string('course_context_description', self::COMPONENT) . ': '
                . content_to_text($course->summary, $course->summaryformat);
        }

        if (!empty($options['dates'])) {
            $lines[] = get_string('course_context_today', self::COMPONENT) . ': '
                . userdate($now, get_string('strftimedaydate', 'langconfig'))
                . ' (' . userdate($now, '%Y-%m-%d') . ')';
        }

        if (!empty($options['contents'])) {
            $structure = self::get_structure($course, $userid, $options, $now);
            if (!empty($structure)) {
                $lines[] = '';
                $lines[] = get_string('course_context_structure', self::COMPONENT);
                $lines = array_merge($lines, $structure);
            }
        }

        $lines[] = get_string('course_context_instruction', self::COMPONENT);

        return implode("\n", $lines);
    }

    /**
     * Build the list of sections and activities visible for the user.
     *
     * @param stdClass $course Course record.
     * @param int $userid User the structure is built for.
     * @param array $options Normalised options.
     * @param int $now Current timestamp.
     * @return string[] Lines describing the structure.
     */
    protected static function get_structure(\stdClass $course, int $userid, array $options, int $now): array {
        $modinfo = get_fast_modinfo($course, $userid);
        $format = course_get_format($course);
        $usedates = !empty($options['dates']);
        $hasdates = $usedates && method_exists($format, 'get_section_dates');
        $deadlines = $usedates ? self::get_deadlines((int)$course->id, $userid, $now) : [];

        $weekstart = strtotime('monday this week', usergetmidnight($now));
        $weekend = $weekstart + 7 * DAYSECS - 1;

        $maxsections = max(0, (int)$options['max_sections']);
        $maxactivities = max(0, (int)$options['max_activities']);
        $sectionslisted = 0;
        $activitieslisted = 0;
        $truncated = false;
        $lines = [];

        foreach ($modinfo->get_section_info_all() as $section) {
            if (!$section->uservisible) {
                continue;
            }

            $visiblecms = [];
            foreach ($section->get_sequence_cm_infos() as $cm) {
                if ($cm->uservisible) {
                    $visiblecms[] = $cm;
                }
            }

            $sectiondates = $hasdates ? self::get_section_dates($format, $section) : null;
            $iscurrent = $sectiondates !== null
                && $now >= $sectiondates['start'] && $now <= $sectiondates['end'];

            // Empty sections are noise, except the one the learner is currently in: knowing that
            // the current week has nothing to do is a valid answer.
            if (empty($visiblecms) && !$iscurrent) {
                continue;
            }

            if ($sectionslisted >= $maxsections) {
                $truncated = true;
                break;
            }
            $sectionslisted++;

            $heading = $format->get_section_name($section);
            if ($sectiondates !== null) {
                $heading .= ' (' . userdate($sectiondates['start'], '%Y-%m-%d')
                    . ' - ' . userdate($sectiondates['end'], '%Y-%m-%d') . ')';
            }
            $lines[] = $heading;

            if (empty($visiblecms)) {
                $lines[] = '  ' . get_string('course_context_noactivities', self::COMPONENT);
                continue;
            }

            foreach ($visiblecms as $cm) {
                if ($activitieslisted >= $maxactivities) {
                    $truncated = true;
                    break 2;
                }
                $activitieslisted++;
                $lines[] = '  - ' . self::describe_activity($cm, $deadlines, $now, $weekstart, $weekend);
            }
        }

        if ($truncated) {
            $lines[] = get_string('course_context_truncated', self::COMPONENT, (object)[
                'sections' => $maxsections,
                'activities' => $maxactivities,
            ]);
        }

        return $lines;
    }

    /**
     * Describe one activity, including its deadline when one is known.
     *
     * @param \cm_info $cm Course module.
     * @param array $deadlines Map of "modname:instance" to timestamp.
     * @param int $now Current timestamp.
     * @param int $weekstart Start of the current week.
     * @param int $weekend End of the current week.
     * @return string Line describing the activity.
     */
    protected static function describe_activity($cm, array $deadlines, int $now, int $weekstart, int $weekend): string {
        $line = self::shorten(format_string($cm->get_name())) . ' [' . $cm->get_module_type_name() . ']';

        $key = $cm->modname . ':' . $cm->instance;
        if (!isset($deadlines[$key])) {
            return $line;
        }

        $due = (int)$deadlines[$key];
        $line .= ', ' . get_string('course_context_due', self::COMPONENT) . ' ' . userdate($due, '%Y-%m-%d %H:%M');
        $line .= ' (' . self::describe_deadline($due, $now, $weekstart, $weekend) . ')';

        return $line;
    }

    /**
     * Classify a deadline relative to the current moment.
     *
     * Kept public so that the three branches can be unit tested with fixed timestamps.
     *
     * @param int $due Deadline timestamp.
     * @param int $now Current timestamp.
     * @param int $weekstart Start of the current week.
     * @param int $weekend End of the current week.
     * @return string Localised description such as "overdue", "this week" or "in 4 days".
     */
    public static function describe_deadline(int $due, int $now, int $weekstart, int $weekend): string {
        if ($due < $now) {
            return get_string('course_context_overdue', self::COMPONENT);
        }

        if ($due >= $weekstart && $due <= $weekend) {
            return get_string('course_context_thisweek', self::COMPONENT);
        }

        return get_string('course_context_indays', self::COMPONENT, (int)floor(($due - $now) / DAYSECS));
    }

    /**
     * Collect the deadlines of a course for a user.
     *
     * Uses the same action events the learner sees in the calendar and the timeline, so third party
     * activity modules are covered without maintaining a map of due date columns.
     *
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @param int $now Current timestamp.
     * @return array Map of "modname:instance" to the most relevant timestamp.
     */
    protected static function get_deadlines(int $courseid, int $userid, int $now): array {
        global $CFG;

        if (!function_exists('calendar_get_events')) {
            require_once($CFG->dirroot . '/calendar/lib.php');
        }

        $events = calendar_get_events(
            $now - self::DEADLINE_WINDOW_PAST,
            $now + self::DEADLINE_WINDOW_FUTURE,
            [$userid],
            false,
            [$courseid]
        );

        $deadlines = [];
        foreach ($events as $event) {
            if (empty($event->modulename) || empty($event->instance)) {
                continue;
            }

            $key = $event->modulename . ':' . $event->instance;
            $timestart = (int)$event->timestart;

            if (!isset($deadlines[$key])) {
                $deadlines[$key] = $timestart;
                continue;
            }

            // Prefer an upcoming date over a past one, and the latest of the remaining candidates:
            // a quiz normally has an opening and a closing date and only the closing one matters.
            $current = $deadlines[$key];
            $currentisupcoming = $current >= $now;
            $newisupcoming = $timestart >= $now;

            if ($newisupcoming && !$currentisupcoming) {
                $deadlines[$key] = $timestart;
            } else if ($newisupcoming === $currentisupcoming && $timestart > $current) {
                $deadlines[$key] = $timestart;
            }
        }

        return $deadlines;
    }

    /**
     * Start and end date of a section, when the course format defines them.
     *
     * @param \course_format $format Course format instance.
     * @param \section_info $section Section.
     * @return array|null Array with start and end keys, or null when the format has no dates.
     */
    protected static function get_section_dates($format, $section): ?array {
        // Section 0 in the weekly format is the general section: the format would report the dates
        // of the first week, which would be misleading.
        if ((int)$section->section < 1) {
            return null;
        }

        try {
            $dates = $format->get_section_dates($section);
        } catch (\Throwable $e) {
            return null;
        }

        if (empty($dates->start) || empty($dates->end)) {
            return null;
        }

        return ['start' => (int)$dates->start, 'end' => (int)$dates->end];
    }

    /**
     * Shorten a text to a bounded length.
     *
     * @param string $text Text to shorten.
     * @return string Shortened text.
     */
    protected static function shorten(string $text): string {
        if (\core_text::strlen($text) <= self::MAX_NAME_LENGTH) {
            return $text;
        }

        return \core_text::substr($text, 0, self::MAX_NAME_LENGTH) . '…';
    }

    /**
     * Read a checkbox style setting, keeping the difference between "not set" and "off".
     *
     * @param string $name Setting name.
     * @param bool $default Value used when the setting was never saved.
     * @return bool
     */
    protected static function get_bool_setting(string $name, bool $default): bool {
        $value = get_config(self::COMPONENT, $name);

        return ($value === false) ? $default : (bool)$value;
    }

    /**
     * Read an integer setting with a default and a lower bound.
     *
     * @param string $name Setting name.
     * @param int $default Value used when the setting was never saved.
     * @return int
     */
    protected static function get_int_setting(string $name, int $default): int {
        $value = get_config(self::COMPONENT, $name);

        return ($value === false) ? $default : max(0, (int)$value);
    }
}
