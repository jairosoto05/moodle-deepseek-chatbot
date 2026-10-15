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
 * Plugin level integrity tests.
 *
 * @package    block_openai_chatbot
 * @category   test
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_openai_chatbot;

/**
 * Plugin level integrity tests.
 *
 * These tests are provider independent: they assert that the plugin is wired correctly and that
 * no OpenAI endpoint or OpenAI credential reaches the network layer any more.
 */
final class plugin_test extends \advanced_testcase {
    /**
     * No plugin PHP or JavaScript file targets an OpenAI API endpoint.
     *
     * @coversNothing
     */
    public function test_no_openai_endpoints_remain(): void {
        $dirroot = __DIR__ . '/..';
        $offenders = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirroot, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (!in_array($file->getExtension(), ['php', 'js', 'mustache'], true)) {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }
            if (preg_match('/api\.openai\.com/i', $contents)) {
                $offenders[] = substr($file->getPathname(), strlen($dirroot) + 1);
            }
        }

        $this->assertSame([], $offenders, 'These files still reference an OpenAI endpoint: ' . implode(', ', $offenders));
    }

    /**
     * The web service keeps its published name, parameters and return structure.
     *
     * @coversNothing
     */
    public function test_web_service_contract_is_unchanged(): void {
        $this->resetAfterTest();

        $functions = \core_external\external_api::external_function_info('block_openai_chatbot_ask_question');

        $this->assertSame('block_openai_chatbot_external', $functions->classname);
        $this->assertSame('ask_question', $functions->methodname);
        $this->assertSame(
            ['question', 'blockinstanceid', 'contextid'],
            array_keys($functions->parameters_desc->keys)
        );
        $this->assertSame(
            ['success', 'message', 'html'],
            array_keys($functions->returns_desc->keys)
        );
    }

    /**
     * The web service answers a question end to end with the course context enabled.
     *
     * The HTTP call is mocked, so this test wires the external function, the course context
     * builder and the DeepSeek client together without contacting the API.
     *
     * @coversNothing
     */
    public function test_ask_question_end_to_end(): void {
        $this->resetAfterTest();

        set_config('deepseekapikey', 'sk-test', 'block_openai_chatbot');
        set_config('coursecontents', 1, 'block_openai_chatbot');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'section' => 1,
            'name' => 'Material de la semana',
        ]);
        $context = \context_course::instance($course->id);
        $block = $this->getDataGenerator()->create_block('openai_chatbot', [
            'parentcontextid' => $context->id,
        ]);

        $this->setUser($student);

        \curl::mock_response(json_encode([
            'id' => 'chat-test',
            'model' => 'deepseek-flash',
            'choices' => [[
                'index' => 0,
                'finish_reason' => 'stop',
                'message' => ['role' => 'assistant', 'content' => 'Tienes que leer el material.'],
            ]],
        ]));

        $result = \block_openai_chatbot_external::ask_question(
            '¿Qué tengo que hacer esta semana?',
            (int)$block->id,
            (int)$context->id
        );

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Tienes que leer el material.', $result['html']);
        $this->assertStringContainsString('chatbot-answer', $result['html']);
    }

    /**
     * The block is recognised by Moodle and exposes its capabilities.
     *
     * @coversNothing
     */
    public function test_block_and_capabilities_are_registered(): void {
        $this->resetAfterTest();

        $block = \core_component::get_component_directory('block_openai_chatbot');
        $this->assertNotNull($block);

        foreach (['addinstance', 'myaddinstance', 'view'] as $capability) {
            $this->assertNotNull(
                get_capability_info('block/openai_chatbot:' . $capability),
                'Missing capability block/openai_chatbot:' . $capability
            );
        }
    }

    /**
     * The OpenAI specific settings are gone and the DeepSeek settings exist.
     *
     * @coversNothing
     */
    public function test_admin_settings_expose_deepseek_configuration(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $settings = [];
        foreach ($DB->get_records('config_plugins', ['plugin' => 'block_openai_chatbot']) as $record) {
            $settings[$record->name] = $record->value;
        }

        // The provider specific settings from the DeepSeek release exist after installation.
        $this->assertArrayHasKey('bot_name', $settings);
        $this->assertArrayHasKey('course_context', $settings);
        $this->assertArrayHasKey('max_response_time', $settings);

        // The OpenAI credentials are not part of the current configuration schema.
        $this->assertArrayNotHasKey('apikey', $settings);
        $this->assertArrayNotHasKey('assistantid', $settings);
    }

    /**
     * The privacy provider reports DeepSeek, not OpenAI, as the external location.
     *
     * @coversNothing
     */
    public function test_privacy_metadata_points_to_deepseek(): void {
        $collection = new \core_privacy\local\metadata\collection('block_openai_chatbot');
        $collection = \block_openai_chatbot\privacy\provider::get_metadata($collection);

        $items = $collection->get_collection();
        $this->assertCount(1, $items);

        /** @var \core_privacy\local\metadata\types\external_location $item */
        $item = reset($items);
        $this->assertSame('deepseek', $item->get_name());
        $this->assertArrayHasKey('question', $item->get_privacy_fields());
    }

    /**
     * The plugin declares the DeepSeek settings that the settings page reads.
     *
     * @coversNothing
     */
    public function test_settings_file_declares_expected_keys(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        global $CFG, $ADMIN;

        require_once($CFG->libdir . '/adminlib.php');

        // Reproduce exactly what core_plugininfolock::load_settings() does with settings.php.
        $ADMIN = admin_get_root(true, true);
        $hassiteconfig = true;
        $settings = new \admin_settingpage('blocksettingopenai_chatbot', 'test');
        include($CFG->dirroot . '/blocks/openai_chatbot/settings.php');

        // Note: admin_setting splits "component/setting" into separate plugin and name properties.
        $names = [];
        foreach ($settings->settings as $setting) {
            $names[] = $setting->plugin . '/' . $setting->name;
        }

        $expected = [
            'block_openai_chatbot/deepseekapikey',
            'block_openai_chatbot/model',
            'block_openai_chatbot/thinkingmode',
            'block_openai_chatbot/reasoningeffort',
            'block_openai_chatbot/temperature',
            'block_openai_chatbot/max_tokens',
            'block_openai_chatbot/systemprompt',
            'block_openai_chatbot/course_context',
            'block_openai_chatbot/coursecontents',
            'block_openai_chatbot/coursecontentsmaxsections',
            'block_openai_chatbot/coursecontentsmaxactivities',
            'block_openai_chatbot/coursecontentsdates',
            'block_openai_chatbot/max_response_time',
        ];
        foreach ($expected as $settingname) {
            $this->assertContains($settingname, $names, 'Missing admin setting ' . $settingname);
        }

        $this->assertNotContains('block_openai_chatbot/apikey', $names);
        $this->assertNotContains('block_openai_chatbot/assistantid', $names);
    }
}
