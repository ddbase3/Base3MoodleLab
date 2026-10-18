# Base3MoodleLab

Moodle project composition and BASE3 administration configuration.

The **Chatbot** section has three tabs:

1. **Configuration**: `chatbot/default`, the global chatbot instance and its
   existing 13 FileManager endpoints.
2. **Launcher**: the shared ClientStack
   `globalchatbotlauncherconfigdisplay` for presentation settings.
3. **Extensions**: the existing chatbot extension settings.

`Base3MoodleGlobalChatbotLauncherOutput` is a discoverable BASE3 `IOutput`
that provides host-specific data attributes for the shared ClientStack ES
module. The Moodle native footer hook renders the output through the existing
BASE3 `Runtime::renderEmbedded()` mechanism. The output uses
`ILinkTargetService` for the regular `chatbotdisplay` URL and `IAssetResolver`
for ClientStack assets; no parallel chatbot service or custom AJAX transport
is introduced.

The Moodle integration deliberately omits `data-launcher-icon` so ClientStack
renders its standard blue pill button with a text label, as on WordPress.

On first render, any stored legacy Moodle launcher window flags are imported
into the canonical `global-chatbot-launcher/default` dataset, unless that
BASE3 dataset already exists. The Moodle site configuration owns launcher
visibility only. Content chatbot records are independent.

Run `php modules/Base3MoodleLab/test/GlobalChatbotLauncherSmokeTest.php` after
updating ClientStack to check module integration and settings. See the root
`README.md`, `PRIVACY.md`, and native plugin `README_CHATBOT.md` for deployment
instructions and security considerations.
