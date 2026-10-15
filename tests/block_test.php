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
 * Tests for the ChatBot block class.
 *
 * @package    block_openai_chatbot
 * @category   test
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_openai_chatbot;


/**
 * Tests for the block class.
 *
 * @covers \block_openai_chatbot
 */
final class block_test extends \advanced_testcase {
    /**
     * Block metadata and rendering.
     *
     * @covers \block_openai_chatbot::get_content
     * @covers \block_openai_chatbot::applicable_formats
     * @covers \block_openai_chatbot::instance_allow_multiple
     * @covers \block_openai_chatbot::has_config
     */
    public function test_block_renders_the_chatbot_interface(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        set_config('bot_name', 'Course Helper', 'block_openai_chatbot');

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $record = $this->getDataGenerator()->create_block('openai_chatbot', [
            'parentcontextid' => $context->id,
        ]);

        $page = new \moodle_page();
        $page->set_url('/course/view.php', ['id' => $course->id]);
        $page->set_context($context);
        $page->set_course($course);
        $page->set_pagelayout('course');
        $page->set_pagetype('course-view-' . $course->format);
        $page->set_title('Test');
        $page->set_heading('Test');

        $block = block_instance('openai_chatbot', $record, $page);
        $this->assertNotFalse($block);

        $this->assertSame('block_openai_chatbot', get_class($block));
        $this->assertTrue($block->has_config());
        $this->assertFalse($block->instance_allow_multiple());
        $this->assertSame(
            ['course' => true, 'course-category' => false, 'site' => false],
            $block->applicable_formats()
        );

        $content = $block->get_content();
        $this->assertNotEmpty($content->text);
        // The configured bot name and the container used by the JavaScript module must be present.
        $this->assertStringContainsString('Course Helper', $content->text);
        $this->assertStringContainsString('chatbot-container', $content->text);
        $this->assertStringContainsString('data-contextid="' . $context->id . '"', $content->text);
        // The old OpenAI specific markup must be gone.
        $this->assertStringNotContainsString('OpenAI', $content->text);
    }
}
