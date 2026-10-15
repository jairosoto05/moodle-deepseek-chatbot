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
 * DeepSeek API client for the ChatBot block.
 *
 * This class talks to the official DeepSeek chat completions endpoint
 * (https://api.deepseek.com/chat/completions) using Moodle's native curl wrapper.
 *
 * @package    block_openai_chatbot
 * @copyright  2025 Esteban Piazza <esteban@codeki.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_openai_chatbot\api;

/**
 * Minimal DeepSeek chat completions client.
 *
 * The class is deliberately free of any provider SDK so that the plugin only requires
 * the standard Moodle HTTP stack. Requests and responses are plain arrays, which makes
 * both of them easy to unit test.
 */
final class deepseek_client {
    /** @var string Official DeepSeek API base URL (OpenAI compatible format). */
    public const API_BASE = 'https://api.deepseek.com';

    /** @var string Chat completions path. */
    public const CHAT_COMPLETIONS_PATH = '/chat/completions';

    /** @var string Model used when nothing else is configured. */
    public const DEFAULT_MODEL = 'deepseek-flash';

    /** @var string Fast, general purpose model. */
    public const MODEL_FLASH = 'deepseek-flash';

    /** @var string Most capable model. */
    public const MODEL_V4_PRO = 'deepseek-v4-pro';

    /** @var int Maximum number of output tokens accepted by the API (384K). */
    public const MAX_TOKENS_LIMIT = 393216;

    /** @var int Default HTTP timeout in seconds when "max_response_time" is not set. */
    public const DEFAULT_TIMEOUT = 30;

    /** @var float Default sampling temperature. */
    public const DEFAULT_TEMPERATURE = 1.0;

    /** @var string Thinking mode enabled (DeepSeek default). */
    public const THINKING_ENABLED = 'enabled';

    /** @var string Thinking mode disabled. */
    public const THINKING_DISABLED = 'disabled';

    /** @var array Supported reasoning effort values when thinking mode is on. */
    public const REASONING_EFFORTS = ['low', 'high', 'max'];

    /** @var string Default reasoning effort. */
    public const DEFAULT_REASONING_EFFORT = 'high';

    /** @var array The chat roles accepted by the endpoint. */
    public const CHAT_ROLES = ['system', 'user', 'assistant'];

    /** @var array Normalised plugin settings. */
    private array $settings;

    /**
     * Constructor.
     *
     * @param array|null $settings Optional settings map. When omitted the values are read
     *                             from the plugin configuration. Passing an array keeps the
     *                             class usable without a Moodle database (unit tests).
     */
    public function __construct(?array $settings = null) {
        $this->settings = self::normalise_settings($settings ?? self::get_plugin_settings());
    }

    /**
     * Read the plugin settings from the Moodle configuration.
     *
     * The legacy "apikey" and "assistantid" values are intentionally not read: they belong
     * to the previous OpenAI Assistant integration and must not be sent to DeepSeek.
     *
     * @return array Raw settings.
     */
    public static function get_plugin_settings(): array {
        $component = 'block_openai_chatbot';

        return [
            'deepseekapikey' => (string)(get_config($component, 'deepseekapikey') ?: ''),
            'model' => (string)(get_config($component, 'model') ?: ''),
            'temperature' => get_config($component, 'temperature'),
            'max_tokens' => get_config($component, 'max_tokens'),
            'thinkingmode' => (string)(get_config($component, 'thinkingmode') ?: ''),
            'reasoningeffort' => (string)(get_config($component, 'reasoningeffort') ?: ''),
            'max_response_time' => get_config($component, 'max_response_time'),
        ];
    }

    /**
     * Validate and complete a settings map.
     *
     * @param array $settings Settings supplied by the caller or by the configuration.
     * @return array Normalised settings.
     */
    public static function normalise_settings(array $settings): array {
        $model = (string)($settings['model'] ?? '');
        if (!array_key_exists($model, self::get_model_choices())) {
            $model = self::DEFAULT_MODEL;
        }

        $thinking = (string)($settings['thinkingmode'] ?? '');
        if (!in_array($thinking, [self::THINKING_ENABLED, self::THINKING_DISABLED], true)) {
            $thinking = self::THINKING_ENABLED;
        }

        $effort = (string)($settings['reasoningeffort'] ?? '');
        if (!in_array($effort, self::REASONING_EFFORTS, true)) {
            $effort = self::DEFAULT_REASONING_EFFORT;
        }

        $temperature = is_numeric($settings['temperature'] ?? null)
            ? (float)$settings['temperature']
            : self::DEFAULT_TEMPERATURE;
        $temperature = max(0.0, min(2.0, $temperature));

        $maxtokens = is_numeric($settings['max_tokens'] ?? null) ? (int)$settings['max_tokens'] : 0;
        $maxtokens = max(0, min(self::MAX_TOKENS_LIMIT, $maxtokens));

        $timeout = is_numeric($settings['max_response_time'] ?? null) ? (int)$settings['max_response_time'] : 0;
        if ($timeout < 1 || $timeout > 300) {
            $timeout = self::DEFAULT_TIMEOUT;
        }

        return [
            'deepseekapikey' => trim((string)($settings['deepseekapikey'] ?? '')),
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxtokens,
            'thinkingmode' => $thinking,
            'reasoningeffort' => $effort,
            'max_response_time' => $timeout,
        ];
    }

    /**
     * Selectable models, ready for an admin select element.
     *
     * @return array Model name => language string identifier.
     */
    public static function get_model_choices(): array {
        return [
            self::MODEL_FLASH => 'model_deepseek_flash',
            self::MODEL_V4_PRO => 'model_deepseek_v4_pro',
        ];
    }

    /**
     * Full chat completions endpoint.
     *
     * @return string Absolute URL.
     */
    public static function get_endpoint(): string {
        return self::API_BASE . self::CHAT_COMPLETIONS_PATH;
    }

    /**
     * Whether an API key is available.
     *
     * @return bool
     */
    public function is_configured(): bool {
        return $this->settings['deepseekapikey'] !== '';
    }

    /**
     * Configured API key. Only the client should need the secret.
     *
     * @return string
     */
    public function get_apikey(): string {
        return $this->settings['deepseekapikey'];
    }

    /**
     * Effective settings.
     *
     * @return array
     */
    public function get_settings(): array {
        return $this->settings;
    }

    /**
     * Build the JSON request body for a chat completion.
     *
     * @param array $messages List of ['role' => string, 'content' => string] entries.
     * @param array $options Optional overrides: model, temperature, max_tokens,
     *                       thinkingmode, reasoningeffort.
     * @return array Request body ready to be JSON encoded.
     * @throws \coding_exception If a message has an invalid shape.
     */
    public function build_request_body(array $messages, array $options = []): array {
        if (empty($messages)) {
            throw new \coding_exception('At least one message is required for a chat completion.');
        }

        $settings = self::normalise_settings(array_merge($this->settings, $options));
        $cleanmessages = [];

        foreach ($messages as $message) {
            if (!is_array($message) || !isset($message['role'], $message['content'])) {
                throw new \coding_exception('Each message must contain a role and a content key.');
            }
            if (!in_array($message['role'], self::CHAT_ROLES, true)) {
                throw new \coding_exception('Unsupported message role: ' . $message['role']);
            }
            $cleanmessages[] = [
                'role' => $message['role'],
                'content' => (string)$message['content'],
            ];
        }

        $body = [
            'model' => $settings['model'],
            'messages' => $cleanmessages,
            'stream' => false,
            'temperature' => $settings['temperature'],
        ];

        if ($settings['max_tokens'] > 0) {
            $body['max_tokens'] = $settings['max_tokens'];
        }

        if ($settings['thinkingmode'] === self::THINKING_DISABLED) {
            // Explicitly opt out of thinking mode: faster and cheaper answers.
            $body['thinking'] = ['type' => self::THINKING_DISABLED];
        } else {
            $body['thinking'] = ['type' => self::THINKING_ENABLED];
            $body['reasoning_effort'] = $settings['reasoningeffort'];
        }

        return $body;
    }

    /**
     * Build the message list sent to the model.
     *
     * The user question is always sent as a separate user message so that it can never
     * be interpreted as part of the system instructions.
     *
     * @param string $systemprompt Administrator provided instructions.
     * @param string $question The user question.
     * @param string $coursecontext Optional course context appended to the instructions.
     * @return array List of messages.
     */
    public static function build_messages(string $systemprompt, string $question, string $coursecontext = ''): array {
        $system = trim($systemprompt);
        $coursecontext = trim($coursecontext);

        if ($coursecontext !== '') {
            $system = $system === '' ? $coursecontext : $system . "\n\n" . $coursecontext;
        }

        $messages = [];
        if ($system !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        return $messages;
    }

    /**
     * Perform a chat completion request against DeepSeek.
     *
     * @param array $messages Messages built with {@see self::build_messages()}.
     * @param array $options Optional generation overrides.
     * @return array Result with content, reasoning, finishreason, model, usage and id keys.
     * @throws \moodle_exception When the plugin is not configured, the request fails or the
     *                          response cannot be used. The exception errorcode is a language
     *                          string identifier of this plugin.
     */
    public function create_chat_completion(array $messages, array $options = []): array {
        if (!$this->is_configured()) {
            throw new \moodle_exception('apikey_missing', 'block_openai_chatbot');
        }

        global $CFG;

        $body = $this->build_request_body($messages, $options);
        $timeout = $this->settings['max_response_time'];

        // The curl wrapper lives in lib/filelib.php and is not part of the class autoloader.
        if (!class_exists('curl')) {
            require_once($CFG->libdir . '/filelib.php');
        }

        $curl = new \curl();
        // Never disable TLS validation, and never follow redirects with the API key attached.
        $curl->setopt([
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_CONNECTTIMEOUT' => min(10, $timeout),
            'CURLOPT_TIMEOUT' => $timeout,
            'CURLOPT_FOLLOWLOCATION' => false,
        ]);
        $curl->setHeader([
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->get_apikey(),
        ]);

        $raw = $curl->post(self::get_endpoint(), json_encode($body));
        $info = $curl->get_info();
        $errno = (int)$curl->get_errno();
        $httpcode = isset($info['http_code']) ? (int)$info['http_code'] : 0;

        if ($errno !== 0) {
            $this->throw_transport_error($errno, (string)$curl->error);
        }

        return $this->parse_response($raw === false ? null : (string)$raw, $httpcode);
    }

    /**
     * Parse a chat completion response.
     *
     * Kept public so that response handling and HTTP error mapping can be unit tested
     * without performing a network request.
     *
     * @param string|null $rawresponse Raw response body.
     * @param int $httpcode HTTP status code.
     * @return array Normalised response.
     * @throws \moodle_exception When the response is an error or is unusable.
     */
    public function parse_response(?string $rawresponse, int $httpcode): array {
        if ($httpcode !== 200) {
            throw new \moodle_exception(
                $this->get_error_code_for_status($httpcode),
                'block_openai_chatbot',
                '',
                null,
                $this->describe_error_response($rawresponse, $httpcode)
            );
        }

        $decoded = json_decode((string)$rawresponse, true);
        if (!is_array($decoded)) {
            throw new \moodle_exception('api_error_invalidjson', 'block_openai_chatbot');
        }

        if (!isset($decoded['choices'][0]['message']) || !is_array($decoded['choices'][0]['message'])) {
            throw new \moodle_exception('api_error_emptyresponse', 'block_openai_chatbot');
        }

        $message = $decoded['choices'][0]['message'];
        $content = isset($message['content']) && is_string($message['content']) ? trim($message['content']) : '';
        if ($content === '') {
            throw new \moodle_exception('api_error_emptyresponse', 'block_openai_chatbot');
        }

        return [
            'content' => $content,
            'reasoning' => isset($message['reasoning_content']) && is_string($message['reasoning_content'])
                ? $message['reasoning_content']
                : null,
            'finishreason' => isset($decoded['choices'][0]['finish_reason'])
                ? (string)$decoded['choices'][0]['finish_reason']
                : '',
            'model' => isset($decoded['model']) ? (string)$decoded['model'] : '',
            'id' => isset($decoded['id']) ? (string)$decoded['id'] : '',
            'usage' => isset($decoded['usage']) && is_array($decoded['usage']) ? $decoded['usage'] : [],
        ];
    }

    /**
     * Map an HTTP status code to a plugin language string.
     *
     * @param int $httpcode HTTP status code.
     * @return string Language string identifier.
     */
    public function get_error_code_for_status(int $httpcode): string {
        switch ($httpcode) {
            case 400:
            case 422:
                return 'api_error_invalidrequest';
            case 401:
                return 'api_error_auth';
            case 402:
                return 'api_error_balance';
            case 429:
                return 'api_error_ratelimit';
            case 500:
            case 502:
            case 503:
            case 504:
                return 'api_error_server';
            default:
                return 'api_error_http';
        }
    }

    /**
     * Build a short, non sensitive description of an error response.
     *
     * The provider message is never forwarded because it may echo part of the API key.
     * Only the error type and code are kept.
     *
     * @param string|null $rawresponse Raw response body.
     * @param int $httpcode HTTP status code.
     * @return string Description for developers.
     */
    private function describe_error_response(?string $rawresponse, int $httpcode): string {
        $summary = 'HTTP ' . $httpcode;

        $decoded = json_decode((string)$rawresponse, true);
        if (is_array($decoded) && isset($decoded['error']) && is_array($decoded['error'])) {
            $type = isset($decoded['error']['type']) ? (string)$decoded['error']['type'] : '';
            $code = isset($decoded['error']['code']) ? (string)$decoded['error']['code'] : '';
            $parts = array_filter([$type, $code]);
            if (!empty($parts)) {
                $summary .= ' (' . implode(', ', $parts) . ')';
            }
        }

        return $this->redact_api_key($summary);
    }

    /**
     * Remove any occurrence of the API key from a string.
     *
     * @param string $text Text that may contain the secret.
     * @return string Redacted text.
     */
    public function redact_api_key(string $text): string {
        $apikey = $this->settings['deepseekapikey'];
        if ($apikey !== '' && strpos($text, $apikey) !== false) {
            $text = str_replace($apikey, '***', $text);
        }

        return $text;
    }

    /**
     * Translate a curl transport error into an exception.
     *
     * @param int $errno Curl error number.
     * @param string $error Curl error message.
     * @return void
     * @throws \moodle_exception Always.
     */
    private function throw_transport_error(int $errno, string $error): void {
        $timeoutcode = defined('CURLE_OPERATION_TIMEDOUT') ? CURLE_OPERATION_TIMEDOUT : 28;
        $errorcode = ($errno === $timeoutcode) ? 'api_error_timeout' : 'api_error_network';

        throw new \moodle_exception(
            $errorcode,
            'block_openai_chatbot',
            '',
            null,
            $this->redact_api_key('curl errno ' . $errno . ': ' . $error)
        );
    }
}
