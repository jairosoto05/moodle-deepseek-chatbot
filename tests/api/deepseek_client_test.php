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

namespace block_openai_chatbot\api;

/**
 * Unit tests for the DeepSeek client: request building, response parsing and error handling.
 *
 * @package    block_openai_chatbot
 * @category   test
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_openai_chatbot\api\deepseek_client
 */
final class deepseek_client_test extends \advanced_testcase {
    /**
     * Build a settings array with a usable API key.
     *
     * @param array $overrides Settings to override.
     * @return array
     */
    private function settings(array $overrides = []): array {
        return array_merge([
            'deepseekapikey' => 'sk-test-key',
            'model' => deepseek_client::DEFAULT_MODEL,
            'temperature' => 0.7,
            'max_tokens' => 0,
            'thinkingmode' => deepseek_client::THINKING_ENABLED,
            'reasoningeffort' => 'high',
            'max_response_time' => 30,
        ], $overrides);
    }

    /**
     * The endpoint points at DeepSeek and never at OpenAI.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::get_endpoint
     */
    public function test_endpoint_targets_deepseek(): void {
        $this->assertSame('https://api.deepseek.com/chat/completions', deepseek_client::get_endpoint());
        $this->assertStringNotContainsString('openai', deepseek_client::get_endpoint());
    }

    /**
     * A request body uses the DeepSeek model and the OpenAI compatible message format.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_request_body
     */
    public function test_build_request_body_uses_deepseek_model(): void {
        $client = new deepseek_client($this->settings());
        $body = $client->build_request_body(deepseek_client::build_messages('Be helpful.', 'What is Moodle?'));

        $this->assertSame('deepseek-flash', $body['model']);
        $this->assertFalse($body['stream']);
        $this->assertCount(2, $body['messages']);
        $this->assertSame('system', $body['messages'][0]['role']);
        $this->assertSame('user', $body['messages'][1]['role']);
        $this->assertSame('What is Moodle?', $body['messages'][1]['content']);
        // The OpenAI Assistants API is gone: no thread or assistant identifiers are sent.
        $this->assertArrayNotHasKey('assistant_id', $body);
        $this->assertArrayNotHasKey('thread_id', $body);
    }

    /**
     * Thinking mode and reasoning effort are sent as documented by DeepSeek.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_request_body
     */
    public function test_build_request_body_thinking_mode(): void {
        $client = new deepseek_client($this->settings([
            'thinkingmode' => deepseek_client::THINKING_ENABLED,
            'reasoningeffort' => 'max',
        ]));
        $body = $client->build_request_body([['role' => 'user', 'content' => 'Hi']]);

        $this->assertSame(['type' => 'enabled'], $body['thinking']);
        $this->assertSame('max', $body['reasoning_effort']);
    }

    /**
     * Disabling thinking mode omits the reasoning effort parameter.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_request_body
     */
    public function test_build_request_body_thinking_disabled_omits_effort(): void {
        $client = new deepseek_client($this->settings(['thinkingmode' => deepseek_client::THINKING_DISABLED]));
        $body = $client->build_request_body([['role' => 'user', 'content' => 'Hi']]);

        $this->assertSame(['type' => 'disabled'], $body['thinking']);
        $this->assertArrayNotHasKey('reasoning_effort', $body);
    }

    /**
     * max_tokens is only sent when it is set.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_request_body
     */
    public function test_build_request_body_max_tokens(): void {
        $default = new deepseek_client($this->settings());
        $this->assertArrayNotHasKey('max_tokens', $default->build_request_body([['role' => 'user', 'content' => 'Hi']]));

        $limited = new deepseek_client($this->settings(['max_tokens' => 512]));
        $this->assertSame(512, $limited->build_request_body([['role' => 'user', 'content' => 'Hi']])['max_tokens']);
    }

    /**
     * Invalid generated values are replaced by safe defaults.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::normalise_settings
     */
    public function test_normalise_settings_clamps_invalid_values(): void {
        $settings = deepseek_client::normalise_settings([
            'deepseekapikey' => '  sk-test-key  ',
            'model' => 'gpt-4o',
            'temperature' => 9,
            'max_tokens' => -5,
            'thinkingmode' => 'maybe',
            'reasoningeffort' => 'insane',
            'max_response_time' => 0,
        ]);

        $this->assertSame('sk-test-key', $settings['deepseekapikey']);
        $this->assertSame(deepseek_client::DEFAULT_MODEL, $settings['model']);
        $this->assertSame(2.0, $settings['temperature']);
        $this->assertSame(0, $settings['max_tokens']);
        $this->assertSame(deepseek_client::THINKING_ENABLED, $settings['thinkingmode']);
        $this->assertSame(deepseek_client::DEFAULT_REASONING_EFFORT, $settings['reasoningeffort']);
        $this->assertSame(deepseek_client::DEFAULT_TIMEOUT, $settings['max_response_time']);
    }

    /**
     * The model choice list only offers DeepSeek models.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::get_model_choices
     */
    public function test_model_choices_are_deepseek_only(): void {
        $this->assertSame(['deepseek-flash', 'deepseek-v4-pro'], array_keys(deepseek_client::get_model_choices()));
    }

    /**
     * Messages put the administrator prompt in the system role and the question in the user role.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_messages
     */
    public function test_build_messages_with_course_context(): void {
        $messages = deepseek_client::build_messages('Be concise.', 'Explain arrays.', 'Course name: PHP 101');

        $this->assertCount(2, $messages);
        $this->assertSame('system', $messages[0]['role']);
        $this->assertStringContainsString('Be concise.', $messages[0]['content']);
        $this->assertStringContainsString('Course name: PHP 101', $messages[0]['content']);
        $this->assertSame(['role' => 'user', 'content' => 'Explain arrays.'], $messages[1]);
    }

    /**
     * Without a system prompt only the user message is sent.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_messages
     */
    public function test_build_messages_without_system_prompt(): void {
        $messages = deepseek_client::build_messages('', 'Hello?');

        $this->assertCount(1, $messages);
        $this->assertSame(['role' => 'user', 'content' => 'Hello?'], $messages[0]);
    }

    /**
     * An empty message list is rejected before any request is made.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::build_request_body
     */
    public function test_build_request_body_rejects_empty_messages(): void {
        $client = new deepseek_client($this->settings());
        $this->expectException(\coding_exception::class);
        $client->build_request_body([]);
    }

    /**
     * A valid chat completion response is parsed into the documented keys.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::parse_response
     */
    public function test_parse_response_success(): void {
        $client = new deepseek_client($this->settings());
        $payload = json_encode([
            'id' => 'chat-1',
            'model' => 'deepseek-flash',
            'choices' => [[
                'index' => 0,
                'finish_reason' => 'stop',
                'message' => [
                    'role' => 'assistant',
                    'content' => '  Moodle is an LMS.  ',
                    'reasoning_content' => 'thinking...',
                ],
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5],
        ]);

        $result = $client->parse_response($payload, 200);

        $this->assertSame('Moodle is an LMS.', $result['content']);
        $this->assertSame('thinking...', $result['reasoning']);
        $this->assertSame('stop', $result['finishreason']);
        $this->assertSame('deepseek-flash', $result['model']);
        $this->assertSame('chat-1', $result['id']);
        $this->assertSame(10, $result['usage']['prompt_tokens']);
    }

    /**
     * Error statuses map to dedicated, user safe language strings.
     *
     * @param int $status HTTP status code.
     * @param string $expected Expected language string identifier.
     * @covers \block_openai_chatbot\api\deepseek_client::parse_response
     * @covers \block_openai_chatbot\api\deepseek_client::get_error_code_for_status
     * @dataProvider http_error_provider
     */
    public function test_parse_response_http_errors(int $status, string $expected): void {
        $client = new deepseek_client($this->settings());

        try {
            $client->parse_response('{"error":{"message":"nope"}}', $status);
            $this->fail('A moodle_exception was expected.');
        } catch (\moodle_exception $e) {
            $this->assertSame($expected, $e->errorcode);
            $this->assertStringNotContainsString('nope', $e->debuginfo ?? '');
        }
    }

    /**
     * Provider status codes and their expected plugin error codes.
     *
     * @return array[]
     */
    public static function http_error_provider(): array {
        return [
            'invalid format' => [400, 'api_error_invalidrequest'],
            'unauthenticated' => [401, 'api_error_auth'],
            'insufficient balance' => [402, 'api_error_balance'],
            'invalid parameters' => [422, 'api_error_invalidrequest'],
            'rate limited' => [429, 'api_error_ratelimit'],
            'server error' => [500, 'api_error_server'],
            'overloaded' => [503, 'api_error_server'],
            'unexpected' => [418, 'api_error_http'],
        ];
    }

    /**
     * A malformed JSON body raises a dedicated error.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::parse_response
     */
    public function test_parse_response_invalid_json(): void {
        $client = new deepseek_client($this->settings());

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('api_error_invalidjson', 'block_openai_chatbot'));
        $client->parse_response('not-json', 200);
    }

    /**
     * A well formed but empty completion raises a dedicated error.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::parse_response
     */
    public function test_parse_response_empty_content(): void {
        $client = new deepseek_client($this->settings());
        $payload = json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => '   ']]]]);

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('api_error_emptyresponse', 'block_openai_chatbot'));
        $client->parse_response($payload, 200);
    }

    /**
     * A response without choices raises a dedicated error.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::parse_response
     */
    public function test_parse_response_missing_choices(): void {
        $client = new deepseek_client($this->settings());

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('api_error_emptyresponse', 'block_openai_chatbot'));
        $client->parse_response(json_encode(['id' => 'chat-1']), 200);
    }

    /**
     * The API key is never included in the message shown for an error.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::redact_api_key
     */
    public function test_error_response_never_leaks_the_api_key(): void {
        $apikey = 'sk-secret-value-123';
        $client = new deepseek_client($this->settings(['deepseekapikey' => $apikey]));

        // DeepSeek echoes the key in its authentication error message.
        $payload = json_encode([
            'error' => [
                'message' => 'Authentication Fails, Your api key: ' . $apikey . ' is invalid',
                'type' => 'authentication_error',
                'code' => 'invalid_request_error',
            ],
        ]);

        try {
            $client->parse_response($payload, 401);
            $this->fail('A moodle_exception was expected.');
        } catch (\moodle_exception $e) {
            $this->assertStringNotContainsString($apikey, (string)$e->debuginfo);
            $this->assertStringNotContainsString('Authentication Fails', (string)$e->debuginfo);
            $this->assertStringContainsString('authentication_error', (string)$e->debuginfo);
        }
    }

    /**
     * Redaction also works on arbitrary text.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::redact_api_key
     */
    public function test_redact_api_key(): void {
        $client = new deepseek_client($this->settings(['deepseekapikey' => 'sk-abc']));
        $this->assertSame('Bearer ***', $client->redact_api_key('Bearer sk-abc'));
        $this->assertSame('nothing to redact', $client->redact_api_key('nothing to redact'));
    }

    /**
     * A missing API key prevents any request from being built.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::create_chat_completion
     */
    public function test_missing_api_key_raises_exception(): void {
        $client = new deepseek_client($this->settings(['deepseekapikey' => '']));

        $this->assertFalse($client->is_configured());
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('apikey_missing', 'block_openai_chatbot'));
        $client->create_chat_completion([['role' => 'user', 'content' => 'Hi']]);
    }

    /**
     * The configuration is read from the plugin settings, not from OpenAI credentials.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::get_plugin_settings
     */
    public function test_get_plugin_settings_reads_deepseek_configuration(): void {
        $this->resetAfterTest();

        set_config('deepseekapikey', 'sk-from-config', 'block_openai_chatbot');
        set_config('model', 'deepseek-v4-pro', 'block_openai_chatbot');
        set_config('apikey', 'sk-legacy-openai', 'block_openai_chatbot');
        set_config('assistantid', 'asst_legacy', 'block_openai_chatbot');

        $settings = deepseek_client::get_plugin_settings();
        $client = new deepseek_client();

        $this->assertSame('sk-from-config', $settings['deepseekapikey']);
        $this->assertSame('deepseek-v4-pro', $settings['model']);
        $this->assertArrayNotHasKey('apikey', $settings);
        $this->assertArrayNotHasKey('assistantid', $settings);
        $this->assertSame('sk-from-config', $client->get_apikey());
    }

    /**
     * An unconfigured plugin reports itself as unconfigured.
     *
     * @covers \block_openai_chatbot\api\deepseek_client::is_configured
     */
    public function test_is_configured(): void {
        $this->resetAfterTest();

        $this->assertFalse((new deepseek_client())->is_configured());

        set_config('deepseekapikey', 'sk-configured', 'block_openai_chatbot');
        $this->assertTrue((new deepseek_client())->is_configured());
    }
}
