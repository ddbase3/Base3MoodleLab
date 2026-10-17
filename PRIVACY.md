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
