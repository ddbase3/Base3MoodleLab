# Base3MoodleLab: data processing and boundaries

Source reviewed: `src/Base3MoodleLabPlugin.php`,
`src/Service/MoodleChatbotConfiguration.php`,
`src/Output/Base3MoodleGlobalChatbotLauncherOutput.php` and
`src/Group/MoodleLabDevelopmentGroup.php` (6 November 2026).

## Project-owned configuration

This project plugin binds the existing Moodle database-backed BASE3
`ISettingsStore` and `IStateStore` and their configured migration runner.
It provides one lazy `chatbotConfigDisplayData` constructor value describing
`chatbot/default` and the existing 13 protected FileManager actions. The
configuration contains Moodle AJAX URLs with session keys and user-visible
labels; it does not transfer or persist upload bytes. The caller may include
a current Moodle session key in the generated endpoint URL, so URLs must
not be sent to untrusted recipients or included in public logs. The client
controls when those endpoints are requested.

The generic `ChatbotconfigSettingsTab` owns the settings form. Shared
ClientStack owns `GlobalChatbotLauncherFeature` with authenticated-group
default and stores the chosen window mode and presentation flags as
`global-chatbot-launcher/default` in the existing BASE3 `ISettingsStore`.
The Moodle footer hook checks the shared feature before displaying the
launcher; the generic Moodle AJAX endpoint checks it before loading
`chatbot/default`. Moodle block/filter chatbots with other identities
remain independent. Existing feature-audience data is not migrated by this
release; Moodle's existing group/feature administration remains the owner.

## Identity and authorization

`MoodleLabDevelopmentGroup` declares the fixed trusted Moodle login `admin`.
Additional developer memberships can be managed through the established
BASE3 group settings, without changing native Moodle site administrator
status. Full group/feature administration requires both effective
`development` membership and native Moodle site administration. The
project does not copy native Moodle roles, credentials, user directories,
course memberships or permissions into a second database.

## Storage, deletion and retention

The project configuration itself creates no file-storage table or directory.
`Base3Moodle` owns completed resources in Moodle's native private File API
area and temporary chunk uploads; see `../Base3Moodle/PRIVACY.md` for the
specific paths and deletion operations. Moodle database settings and
launcher flags persist until edited or removed according to the host's
policy. ClientStack can use browser `localStorage` and `sessionStorage` for
window modes and reopen behavior, depending on the saved configuration.
Neither this project module nor the source snapshot specifies log, browser,
backup or orphan-upload retention periods. Removing a feature override or
source-defined bootstrap user does not automatically erase database backups.

## Recipients, transfers, security and accessibility

This project module introduces no new remote recipient, telemetry backend
or cross-border transfer. Actual AI model endpoints and other installed
plugins may process conversation content remotely and must be assessed on
the deployed Moodle site. Host sessions, Moodle `sesskey`, native role
capabilities, BASE3 `IAuthorizationService` and the existing protected
FileManager endpoint govern access. The shared launcher and settings UI
must be tested with keyboard, screen reader and actual Moodle theme against
BASE3 WCAG 2.2 AA. This source review is not a deployment legal-compliance
assertion; operators must determine their own GDPR/ePrivacy and retention
obligations.
