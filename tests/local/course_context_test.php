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
 * Tests for the course context builder.
 *
 * @package    block_openai_chatbot
 * @category   test
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_openai_chatbot\local;

/**
 * Tests for the course context builder.
 *
 * @covers \block_openai_chatbot\local\course_context
 */
final class course_context_test extends \advanced_testcase {
    /**
     * Full options with everything enabled, used as the base of most tests.
     *
     * @param array $overrides Options to override.
     * @return array
     */
    private function options(array $overrides = []): array {
        return array_merge([
            'enabled' => true,
            'contents' => true,
            'dates' => true,
            'max_sections' => 10,
            'max_activities' => 40,
        ], $overrides);
    }

    /**
     * Create a course, a student enrolled in it and return both.
     *
     * @param array $courserecord Extra course fields.
     * @return array [course, student]
     */
    private function create_course_with_student(array $courserecord = []): array {
        $course = $this->getDataGenerator()->create_course($courserecord);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        return [$course, $student];
    }

    /**
     * The name and the description of the course are always sent.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_course_name_and_description_are_sent(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student([
            'fullname' => 'Biología 101',
            'summary' => '<p>Introducción a la célula</p>',
            'summaryformat' => FORMAT_HTML,
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringContainsString('Biología 101', $text);
        $this->assertStringContainsString('Introducción a la célula', $text);
        $this->assertStringNotContainsString('<p>', $text);
    }

    /**
     * The visible activities of the course are listed.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_visible_activities_are_listed(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $this->getDataGenerator()->create_module('forum', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Foro de dudas',
        ]);
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Apunte de la semana',
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringContainsString('Foro de dudas', $text);
        $this->assertStringContainsString('Apunte de la semana', $text);
        $this->assertStringContainsString(get_string('course_context_structure', 'block_openai_chatbot'), $text);
    }

    /**
     * A hidden activity is never sent to the model.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_hidden_activity_is_not_sent(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Contenido oculto',
            'visible' => 0,
        ]);
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Contenido visible',
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringContainsString('Contenido visible', $text);
        $this->assertStringNotContainsString('Contenido oculto', $text);
    }

    /**
     * An activity restricted by an availability condition is not sent to a student.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_activity_restricted_by_availability_is_not_sent(): void {
        global $DB;

        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $mod = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Todavía no disponible',
        ]);

        // Not available until well into the future. The stored format is the availability tree:
        // an operator, its conditions and the "show condition" flags.
        $availability = json_encode([
            'op' => '&',
            'c' => [
                \availability_date\condition::get_json(
                    \availability_date\condition::DIRECTION_FROM,
                    time() + 60 * DAYSECS
                ),
            ],
            'showc' => [true],
        ]);
        $DB->set_field('course_modules', 'availability', $availability, ['id' => $mod->cmid]);
        rebuild_course_cache($course->id, true);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringNotContainsString('Todavía no disponible', $text);
    }

    /**
     * A hidden section is not sent to the model.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_hidden_section_is_not_sent(): void {
        global $DB;

        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Página de la sección uno',
        ]);
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1], '*', MUST_EXIST);
        $DB->set_field('course_sections', 'visible', 0, ['id' => $section->id]);
        rebuild_course_cache($course->id, true);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringNotContainsString('Página de la sección uno', $text);
    }

    /**
     * Deadlines coming from the calendar are included next to the activity.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_deadlines_are_included(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $duedate = time() + 20 * DAYSECS;
        $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Informe final',
            'duedate' => $duedate,
            'grade' => 100,
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringContainsString('Informe final', $text);
        $this->assertStringContainsString(get_string('course_context_due', 'block_openai_chatbot'), $text);
        $this->assertStringContainsString(userdate($duedate, '%Y-%m-%d'), $text);
    }

    /**
     * The deadlines are not included when the dates option is disabled.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_deadlines_are_omitted_when_dates_disabled(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Informe final',
            'duedate' => time() + 20 * DAYSECS,
            'grade' => 100,
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options(['dates' => false]));

        $this->assertStringContainsString('Informe final', $text);
        $this->assertStringNotContainsString(get_string('course_context_due', 'block_openai_chatbot'), $text);
        $this->assertStringNotContainsString(userdate(time(), '%Y-%m-%d'), $text);
    }

    /**
     * The three deadline classifications.
     *
     * @covers \block_openai_chatbot\local\course_context::describe_deadline
     */
    public function test_describe_deadline(): void {
        $now = 1000000;
        $weekstart = $now + 100;
        $weekend = $now + 100000;

        $this->assertSame(
            get_string('course_context_overdue', 'block_openai_chatbot'),
            course_context::describe_deadline($now - 1, $now, $weekstart, $weekend)
        );
        $this->assertSame(
            get_string('course_context_thisweek', 'block_openai_chatbot'),
            course_context::describe_deadline($now + 500, $now, $weekstart, $weekend)
        );
        $this->assertSame(
            get_string('course_context_indays', 'block_openai_chatbot', 2),
            course_context::describe_deadline($now + 2 * DAYSECS, $now, $weekstart, $weekend)
        );
    }

    /**
     * The configured limits are respected and the truncation is announced.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_limits_and_truncation(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        for ($section = 1; $section <= 3; $section++) {
            for ($i = 1; $i <= 3; $i++) {
                $this->getDataGenerator()->create_module('page', [
                    'course' => $course->id,
                    'section' => $section,
                    'name' => "Actividad {$section}-{$i}",
                ]);
            }
        }

        $text = course_context::build_for_course($course, (int)$student->id, $this->options([
            'max_sections' => 2,
            'max_activities' => 4,
        ]));

        // Four activities fit (1-1, 1-2, 1-3 and 2-1), the fifth one does not.
        $this->assertStringContainsString('Actividad 1-1', $text);
        $this->assertStringContainsString('Actividad 1-2', $text);
        $this->assertStringContainsString('Actividad 1-3', $text);
        $this->assertStringContainsString('Actividad 2-1', $text);
        $this->assertStringNotContainsString('Actividad 2-2', $text);
        $this->assertStringNotContainsString('Actividad 3-1', $text);
        $this->assertStringContainsString('truncated', $text);
    }

    /**
     * The structure of another course is never included.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_other_course_is_not_included(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        [$other] = $this->create_course_with_student();
        $this->getDataGenerator()->enrol_user($student->id, $other->id, 'student');
        $this->getDataGenerator()->create_module('page', [
            'course' => $other->id,
            'section' => 1,
            'name' => 'Actividad de otro curso',
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        $this->assertStringNotContainsString('Actividad de otro curso', $text);
    }

    /**
     * A teacher does see hidden activities, because that is what uservisible means.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_teacher_receives_hidden_activities(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Borrador del profesor',
            'visible' => 0,
        ]);

        $text = course_context::build_for_course($course, (int)$teacher->id, $this->options());

        $this->assertStringContainsString('Borrador del profesor', $text);
    }

    /**
     * The whole feature can be disabled.
     *
     * @covers \block_openai_chatbot\local\course_context::build
     */
    public function test_disabled_returns_empty_string(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        $context = \context_course::instance($course->id);

        $text = course_context::build($context, (int)$student->id, $this->options(['enabled' => false]));
        $this->assertSame('', $text);

        $text = course_context::build($context, (int)$student->id, $this->options(['contents' => false]));
        $this->assertStringContainsString($course->fullname, $text);
        $this->assertStringNotContainsString(get_string('course_context_structure', 'block_openai_chatbot'), $text);
    }

    /**
     * The current week is listed even when it has no activities, so that "this week" has an answer.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_current_week_is_listed_when_empty(): void {
        $this->resetAfterTest();

        // A weekly course that started two weeks ago: the current section is week 3.
        [$course, $student] = $this->create_course_with_student([
            'format' => 'weeks',
            'numsections' => 4,
            'startdate' => time() - 14 * DAYSECS,
        ]);
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Material de la semana uno',
        ]);

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        // Week 3 is empty but it is the current week, so it must be announced with no activities.
        $format = course_get_format($course);
        $section3 = get_fast_modinfo($course, $student->id)->get_section_info(3);
        $this->assertStringContainsString($format->get_section_name($section3), $text);
        $this->assertStringContainsString(get_string('course_context_noactivities', 'block_openai_chatbot'), $text);
        // Week 4 is empty and in the future, so it is left out.
        $section4 = get_fast_modinfo($course, $student->id)->get_section_info(4);
        $this->assertStringNotContainsString($format->get_section_name($section4) . ' (', $text);
    }

    /**
     * The generated text stays inside a reasonable size for a normal course.
     *
     * @covers \block_openai_chatbot\local\course_context::build_for_course
     */
    public function test_generated_text_is_bounded(): void {
        $this->resetAfterTest();

        [$course, $student] = $this->create_course_with_student();
        for ($section = 1; $section <= 10; $section++) {
            for ($i = 1; $i <= 5; $i++) {
                $this->getDataGenerator()->create_module('page', [
                    'course' => $course->id,
                    'section' => $section,
                    'name' => "Actividad {$section}-{$i}",
                ]);
            }
        }

        $text = course_context::build_for_course($course, (int)$student->id, $this->options());

        // 40 activities is the default cap.
        $this->assertSame(40, substr_count($text, '  - '));
        $this->assertLessThan(8000, strlen($text));
    }
}
