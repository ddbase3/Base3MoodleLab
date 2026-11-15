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
BASE3 dataset already exists. `Base3\ClientStack\GlobalChatbotLauncher\GlobalChatbotLauncherFeature` is the single
feature definition for the floating launcher. Its default audience is the
BASE3 `authenticated` group; operators can change it under BASE3 > Features.
The former native Moodle visibility settings are not read anymore. Content
chatbots with different identities remain independent. The existing Moodle
AJAX transport enforces the same feature when loading `chatbot/default`.

Run `php modules/Base3MoodleLab/test/GlobalChatbotLauncherSmokeTest.php` after
updating ClientStack to check module integration and settings. See the root
`README.md`, `PRIVACY.md`, and native plugin `README_CHATBOT.md` for deployment
instructions and security considerations.

## Moodle authorization composition

The project now composes the current BASE3 authorization model without defining
another access evaluator. `Base3Moodle` owns the Moodle adapters for native
roles, capabilities, and groups. `Base3MoodleLabPlugin` now contributes
only the project-owned `MoodleChatbotConfiguration` through the existing
`chatbotConfigDisplayData` dependency. Generic `ISettingsTab` classes in
their owning modules define the navigation and visibility. Native Moodle
`moodle/site:config` is required for the administration and the effective
BASE3 `development` audience is required for privileged editing.

Configure exact initial Moodle login names (preferred), or verified complete
email addresses, in `src/Group/MoodleLabDevelopmentGroup.php` BEFORE hiding
Groups/Features. The code-defined bootstrap members are never removed by the
host-managed group rules; additional members can be assigned via native
Moodle role, user or criterion. A BASE3 developer still needs Moodle-native
site administrator rights to edit Groups and Features. Games retain their independent feature-based availability through
their owning BASE3 modules and `IAuthorizationService`.

The shared `authorizationadministrationdisplay` is a read-only inspector of
native `CAP_ALLOW` role-capability assignments by Moodle context ID; Moodle
retains role administration. **Groups** and **Features** use the exact common
ClientStack displays `authorizationgroupsdisplay` and
`authorizationfeaturesdisplay`, not Moodle-specific displays or templates.
`Base3Moodle` implements their UiFoundation data/administration contract using
the existing Moodle-backed `ISettingsStore` and native Moodle authorization.
See `../Base3Moodle/README.md` and `PRIVACY.md`.

## Current project composition and removal (6 November 2026)

The obsolete static settings interface and `Base3MoodleLabSettings` are removed
with the supplied idempotent source cleanup script. Project configuration
is defined once in `MoodleChatbotConfiguration` and injected into the generic
`ChatbotconfigSettingsTab` through the existing classmap constructor argument
`chatbotConfigDisplayData`. No parallel settings UI or AJAX endpoint exists.
The duplicate project `GlobalChatbotLauncherFeature` is removed: only the
ClientStack-owned feature is discoverable. Existing feature audience overrides
are not migrated, as requested for this development site.

The Moodle footer hook remains the sole global launcher entry point and uses
the unmodified ClientStack browser assets. Native host capability checks and
the owner-scoped FileManager route remain in Base3Moodle. See
`test/GlobalChatbotLauncherSmokeTest.php` for composition verification.
