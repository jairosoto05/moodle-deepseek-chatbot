# ChatBot Block for Moodle (DeepSeek)

A Moodle block that connects a course chatbot to the **DeepSeek API**, providing AI powered
tutoring and course-specific assistance directly inside Moodle courses.

> **Migration notice.** This block was previously built on the OpenAI Assistant API. The
> Frankenstyle component name is still `block_openai_chatbot` on purpose: keeping it means
> Moodle recognises existing installations and no database table, capability key or block
> instance is lost. Only the AI provider changed.

## Features

- 🤖 **DeepSeek chat completions**: uses the official `https://api.deepseek.com/chat/completions` endpoint.
- 🧠 **Model selection**: choose between `deepseek-flash` and `deepseek-v4-pro` in the admin settings.
- ⚙️ **Generation controls**: thinking mode, reasoning effort, temperature and maximum output tokens.
- 📚 **Course context awareness**: optionally sends the course name, summary, sections, the activities the user can see and their deadlines, so the assistant can answer "what do I have to do this week?".
- 💬 **System prompt**: administrator-defined instructions for the assistant.
- 🌍 **Multi-language support**: English and Spanish.
- 🎨 **Modern UI**: clean, responsive design that integrates with Moodle themes.
- 🔒 **Secure**: the API key is stored with the Moodle configuration API, masked in the admin UI,
  never sent to regular users and never written to the logs. TLS verification is explicitly enabled.

## Requirements

- Moodle 4.1 or later (developed and verified against Moodle 5.2).
- A DeepSeek account with an API key: <https://platform.deepseek.com/>
- The PHP cURL extension (already required by Moodle).

## Installation

### Method 1: ZIP installation (recommended)

1. Download or build the plugin ZIP.
2. Go to **Site administration** → **Plugins** → **Install plugins**.
3. Upload the ZIP file and follow the installation wizard.

### Method 2: Manual installation

1. Extract the plugin files.
2. Copy the `openai_chatbot` folder to `[moodle]/blocks/`.
3. Go to **Site administration** → **Notifications** to complete the installation.

### Upgrading from the OpenAI version

No manual database step is required:

1. Replace the contents of `blocks/openai_chatbot` with this version.
2. Go to **Site administration** → **Notifications** and run the upgrade.
3. Enter a DeepSeek API key in the block settings (see below).

What the upgrade changes:

| Setting | Action | Reason |
| --- | --- | --- |
| `apikey` (OpenAI) | Removed | An OpenAI key cannot be used with DeepSeek. It is deleted so that it can never be sent to the DeepSeek endpoint. |
| `assistantid` (OpenAI) | Removed | DeepSeek has no Assistant objects; the assistant behaviour is now defined by the system prompt. |
| `bot_name` | Kept | Provider independent. |
| `course_context` | Kept | Provider independent. |
| `max_response_time` | Kept | Provider independent, now also used as the HTTP timeout. |
| `deepseekapikey`, `model`, `thinkingmode`, `reasoningeffort`, `temperature`, `max_tokens`, `systemprompt` | Added with defaults | New DeepSeek settings. |
| Web service name | Renamed from `OpenAI ChatBot Service` to `ChatBot Service` | The database row is renamed in place before Moodle synchronises `db/services.php`, so the service and any access token issued for it are preserved. |

Block instances, capabilities (`block/openai_chatbot:view`, `:addinstance`, `:myaddinstance`),
the `block_openai_chatbot_ask_question` web service function and its parameters are unchanged, so
existing courses, roles and mobile app tokens keep working.

## Configuration

### 1. Get a DeepSeek API key

1. Create an account at [DeepSeek Platform](https://platform.deepseek.com/).
2. Generate an API key in the API keys section of the platform.
3. Copy the key.

### 2. Configure the plugin

1. Go to **Site administration** → **Plugins** → **Blocks** → **ChatBot (DeepSeek)**.
2. Enter the **DeepSeek API key**.
3. Select the **Model** (`deepseek-flash` by default).
4. Optionally adjust:
   - **Thinking mode** and **Reasoning effort**: depth of reasoning versus speed and cost.
   - **Temperature**: 0 to 2; it has no effect while thinking mode is enabled.
   - **Maximum output tokens**: 0 (DeepSeek default) up to 393216.
   - **System prompt**: instructions that define the assistant behaviour.
   - **Enable course context**: send the course name and summary with each question.
   - **Include the course structure**: send the sections and the activities the user can see.
   - **Maximum sections / activities**: cap the size of that structure (defaults 10 and 40).
   - **Include dates and deadlines**: send the current date and the due/closing dates of the activities.
   - **Maximum response time**: HTTP timeout in seconds (default 30).

### 3. Add the block to courses

1. Navigate to a course where you have editing permissions.
2. Turn **editing on**.
3. Click **Add a block** and select **ChatBot (DeepSeek)**.

## Usage

- **Students**: type a question in the block; the answer comes from DeepSeek, contextualised to
  the course when course context is enabled.
- **Teachers**: same interface.
- **Administrators**: adjust the settings above; costs are managed in the
  [DeepSeek platform](https://platform.deepseek.com/).

## Architecture

```
blocks/openai_chatbot/
├── block_openai_chatbot.php        # Block class and server side fallback
├── version.php                     # Plugin version, component name, supported Moodle branches
├── settings.php                    # Admin configuration (API key, model, generation, prompt)
├── index.php                       # Configuration status page (never shows the key)
├── styles.css                      # Block styling
├── classes/
│   ├── api/deepseek_client.php     # DeepSeek chat completions client
│   ├── local/course_context.php    # Course context sent to the model
│   ├── external.php                # Web service block_openai_chatbot_ask_question
│   ├── output/chatbot_interface.php
│   ├── output/renderer.php
│   └── privacy/provider.php        # Privacy metadata (external location: DeepSeek)
├── db/
│   ├── access.php                  # Capabilities (unchanged)
│   ├── services.php                # Web service and service definition
│   └── upgrade.php                 # Migration from the OpenAI settings
├── amd/src/chatbot.js              # Frontend module (calls the web service)
├── amd/build/chatbot.min.js        # Built frontend module
├── templates/chatbot_interface.mustache
├── lang/en, lang/es
└── tests/                          # PHPUnit tests
```

Request flow: browser → `core/ajax` → `block_openai_chatbot_ask_question` →
`block_openai_chatbot\api\deepseek_client` → `https://api.deepseek.com/chat/completions`.

## Security & Privacy

- ✅ No API keys in the source code; the key is only stored in the Moodle configuration.
- ✅ The API key is entered through a masked admin field and is never rendered to regular users.
- ✅ The key and the `Authorization` header are never logged; provider error messages are
  redacted before they can reach a log or a user.
- ✅ TLS certificate and host verification are explicitly enabled and redirects are not followed.
- ✅ Questions and answers are escaped before they are inserted into HTML.
- ✅ Capability checks (`block/openai_chatbot:view`) and context validation are preserved.
- ⚠️ **Privacy**: user questions and, when enabled, the course name, the summary, the course
  structure (only the sections and activities that user can see) and the activity deadlines are
  transmitted to DeepSeek for processing. The plugin stores no conversation history. Make sure this
  complies with your institution's privacy policy.

## Troubleshooting

**"The DeepSeek API is not configured yet"**
- Enter a DeepSeek API key in the block settings.

**"The DeepSeek API key is invalid"**
- The key was rejected (HTTP 401). Generate a new key at <https://platform.deepseek.com/>.

**"The DeepSeek account has no remaining balance"**
- Top up the DeepSeek account (HTTP 402).

**"The DeepSeek API is receiving too many requests"**
- You reached the concurrency or rate limit (HTTP 429). Retry after a short wait.

**Timeout errors**
- Increase **Maximum response time**, or disable thinking mode for shorter answers.

**Block not appearing**
- Check that you have permission to add blocks and refresh the course page.

## Development

Run the plugin test suite from a Moodle installation with PHPUnit configured:

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite block_openai_chatbot_testsuite
```

The 48 tests cover request building, response parsing, HTTP error mapping, missing and invalid API
keys, API key redaction, configuration defaults, block rendering, the course context builder
(visibility, availability, deadlines, limits) and the absence of any remaining OpenAI endpoint in the
plugin code.

Coding style (Moodle standard) can be checked with
[moodle-cs](https://github.com/moodlehq/moodle-cs):

```bash
phpcs --standard=moodle --extensions=php blocks/openai_chatbot
```

## License

This plugin is licensed under the [GNU GPL v3](http://www.gnu.org/copyleft/gpl.html).

## Credits

Developed for the Moodle Community - 2025.

## Changelog

### Version 2.0.0
- Migrated the AI provider from the OpenAI Assistant API to the DeepSeek chat completions API.
- Added the DeepSeek client with request building, response parsing and HTTP error mapping.
- Added model selection, thinking mode, reasoning effort, temperature, maximum output tokens and
  a configurable system prompt.
- Removed the OpenAI `apikey` and `assistantid` settings through an upgrade step.
- Updated language files, privacy metadata and documentation, and added PHPUnit tests.

### Version 1.0.0
- Initial release with the OpenAI Assistant API v2 integration.