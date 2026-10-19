# Base3MoodleLab: verified configuration behavior

Source reviewed: `src/Base3MoodleLabPlugin.php` and
`src/Service/Base3MoodleLabSettings.php` (17 October 2026).

This project plugin provides administration display configuration to the
Moodle BASE3 runtime. The chatbot resource display keeps storage owner
`chatbot/default` and configures 13 URLs pointing to the generic BASE3
output `base3moodlefilemanageroutput`. Those URLs carry the current Moodle
session key and the requested FileManager action. No content is uploaded
through the settings provider itself; file content is handled by the
Base3Moodle storage and upload services.

The change introduces no new recipients, transfers, storage tables or files,
and no separate retention process. The deployed Moodle session, logs, other
installed modules, remote AI services and backups need separate assessment.
Deletion of chatbot files is performed by the existing FileManager/storage
services, not by this project configuration module. Administrator and
client-side accessibility behavior should be verified in the deployment.

## Shared global chatbot launcher (18 October 2026)

The project module now exposes the `base3moodleglobalchatbotlauncheroutput`
BASE3 `IOutput`. It renders public configuration attributes for the common
ClientStack launcher, including the existing `chatbot/default` identity,
window flags, the standard BASE3 chatbot display URL, and language labels.
No new service endpoint, file storage backend, AI provider, conversation store
or recipient is introduced.

Launcher flags are read from the existing database-backed BASE3 `SettingsStore`
as `global-chatbot-launcher/default`. A one-time import of previously saved
native Moodle window flags runs only if this central dataset is absent.
The Moodle native visibility flag remains separate. ClientStack can store
mode/size choices in `localStorage` and open-state in `sessionStorage` as
configured. Actual retention, browser configuration and deployment-level
notices must be assessed by the Moodle operator. ClientStack provides the
interaction and keyboard controls; verify theme integration for accessibility.

## Launcher button appearance (18 October 2026)

The Moodle launcher no longer passes the site-local chatbot icon to
ClientStack. Its standard labeled button is rendered instead. This is
a presentation-only change; identity, access controls, browser storage,
request destinations and retention remain unchanged.

## Authorization navigation and development bootstrap (19 October 2026)

`Base3MoodleLabSettings` reads the current effective BASE3 groups via
`IUsermanager` to select the full or reduced site-administration navigation.
This navigation filtering does not store the evaluated group list. Endpoint
permissions remain checked separately by the Moodle runtime and the Moodle
Group/Feature administration displays.

`MoodleLabDevelopmentGroup` may contain exact Moodle login names or complete
email addresses of trusted maintainers in PHP source. The distributed lists
are empty by default. Configured identities may appear in repositories,
build artifacts, deployment copies and backups; maintainers must limit access
and update deployed source to revoke this fixed membership. Group members
added through the UI are stored in the host-owned BASE3 settings datasets;
removing one source of membership does not remove independent OR alternatives.
An email-domain match is not an automatic administrator grant.

No new independent user database, native Moodle role storage, network
recipient or cross-border transfer is introduced by the project composition.
Retention, deletion of backups, native audit trails and user notifications
remain dependent on the actual Moodle deployment and its configured services.
The site owner must assess them in its deployment privacy documentation.
