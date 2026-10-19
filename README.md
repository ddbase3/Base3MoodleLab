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

## Moodle authorization composition

The project now composes the current BASE3 authorization model without defining
another access evaluator. `Base3Moodle` owns the Moodle adapters for native
roles, capabilities, and groups; `Base3MoodleLabSettings` owns the final
administration catalog and its visible navigation. Moodle site administrators
without a BASE3 `development` membership see reduced navigation: BASE3
Permissions and Games. The full administration catalog is visible only to
members of the effective `development` group.

Configure exact initial Moodle login names (preferred), or verified complete
email addresses, in `src/Group/MoodleLabDevelopmentGroup.php` BEFORE hiding
Groups/Features. The code-defined bootstrap members are never removed by the
host-managed group rules; additional members can be assigned via native
Moodle role, user or criterion. A BASE3 developer still needs Moodle-native
site administrator rights to edit Groups and Features. Games are not generic
administration endpoints, and PulseCore controls its own availability through
`IAuthorizationService` and `PulseCorePlayFeature`.

The shared `authorizationadministrationdisplay` is a read-only inspector of
native `CAP_ALLOW` role-capability assignments by Moodle context ID; Moodle
retains role administration. New editable BASE3 groups and feature audiences
are in `Base3Moodle`, using the existing Moodle-backed `ISettingsStore`.
See `../Base3Moodle/README.md` and `PRIVACY.md`.
