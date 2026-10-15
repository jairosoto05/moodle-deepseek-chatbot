# OpenAI ChatBot Block for Moodle

A powerful Moodle block that integrates OpenAI's Assistant API to provide intelligent tutoring and course-specific assistance directly within Moodle courses.

## Features

- 🤖 **OpenAI Assistant Integration**: Uses OpenAI's latest Assistant API v2
- 📚 **Course Context Awareness**: Provides responses tailored to specific course content
- 🌍 **Multi-language Support**: Available in English and Spanish
- 🎨 **Modern UI**: Clean, responsive design that integrates seamlessly with Moodle themes
- ⚙️ **Easy Configuration**: Simple admin interface for API key and assistant setup
- 🔒 **Secure**: No hardcoded API keys, all configuration through Moodle admin

## Requirements

- Moodle 4.1 or later
- OpenAI API account with Assistant API access
- PHP cURL extension enabled

## Installation

### Method 1: ZIP Installation (Recommended)

1. Download the plugin ZIP file
2. Go to **Site administration** → **Plugins** → **Install plugins**
3. Upload the ZIP file and follow the installation wizard

### Method 2: Manual Installation

1. Extract the plugin files
2. Copy the `openai_chatbot` folder to `[moodle]/blocks/`
3. Go to **Site administration** → **Notifications** to complete the installation

## Configuration

### 1. Get OpenAI Credentials

1. Create an account at [OpenAI Platform](https://platform.openai.com/)
2. Generate an API key from the [API Keys page](https://platform.openai.com/api-keys)
3. Create an Assistant from the [Assistants page](https://platform.openai.com/assistants)
4. Copy your Assistant ID

### 2. Configure the Plugin

1. Go to **Site administration** → **Plugins** → **Blocks** → **OpenAI ChatBot**
2. Enter your **OpenAI API Key**
3. Enter your **Assistant ID**
4. Configure other settings as needed:
   - **Enable Course Context**: Include course information in responses
   - **Maximum Response Time**: Timeout for API responses (default: 30 seconds)

### 3. Add Block to Courses

1. Navigate to any course
2. Turn **editing on**
3. Click **Add a block**
4. Select **OpenAI ChatBot**
5. The block will appear in the course sidebar

## Usage

### For Students
- Type questions in the chatbot interface
- Get instant AI-powered responses
- Responses are contextual to the current course content

### For Teachers
- Same interface as students
- Can help answer complex course-related questions
- Provides additional tutoring support

### For Administrators
- Monitor usage through Moodle logs
- Adjust configuration settings
- Manage API costs through OpenAI dashboard

## Customization

### Assistant Behavior
Customize your assistant's behavior by:
1. Going to [OpenAI Assistants](https://platform.openai.com/assistants)
2. Editing your assistant's instructions
3. Adding knowledge base files
4. Configuring model parameters

### Block Appearance
- The block inherits your Moodle theme styling
- Custom CSS can be added through **Appearance** → **Themes** → **Advanced settings**

## Security & Privacy

- ✅ No API keys stored in code
- ✅ All configuration through Moodle admin interface
- ✅ User questions are sent to OpenAI for processing
- ✅ No personal data stored locally by the plugin
- ⚠️ Questions and responses pass through OpenAI's servers

### Privacy Considerations
This plugin sends user questions to OpenAI's servers for processing. Please ensure your users are aware of this and that it complies with your institution's privacy policies.

## Troubleshooting

### Common Issues

**"Configuration missing" error**
- Check that API key and Assistant ID are correctly entered
- Verify your OpenAI account has API access

**"Timeout" errors**
- Increase the Maximum Response Time setting
- Check your internet connection
- Verify OpenAI service status

**Block not appearing**
- Ensure plugin is properly installed
- Check that you have permission to add blocks
- Try refreshing the course page

### Getting Help

1. Check the [OpenAI API Status](https://status.openai.com/)
2. Review your OpenAI API usage and limits
3. Check Moodle error logs for detailed error messages

## Development

### File Structure
```
blocks/openai_chatbot/
├── block_openai_chatbot.php    # Main block class
├── version.php                 # Plugin version and dependencies
├── settings.php               # Admin configuration interface
├── styles.css                 # Block styling
├── chatbot.js                 # Frontend JavaScript
├── lang/                      # Language files
│   ├── en/
│   └── es/
└── README.md                  # This file
```

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This plugin is licensed under the [GNU GPL v3](http://www.gnu.org/copyleft/gpl.html).

## Credits

Developed for the Moodle Community - 2025

## Changelog

### Version 1.0.0
- Initial release
- OpenAI Assistant API v2 integration
- Course context awareness
- Multi-language support (EN/ES)
- Admin configuration interface

---

For support and updates, visit our [GitHub repository](https://github.com/your-username/moodle-block-openai-chatbot).