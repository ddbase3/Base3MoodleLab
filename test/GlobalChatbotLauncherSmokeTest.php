<?php declare(strict_types=1);

/** CLI smoke test of project composition and the shared ClientStack launcher. */

$root = dirname(__DIR__, 3);
spl_autoload_register(static function(string $class) use ($root): void {
	$parts = explode('\\', $class, 2);
	$module = $parts[0] === 'Base3' ? 'Base3Framework' : $parts[0];
	$relative = $parts[0] === 'Base3' ? ($parts[1] ?? '') : ($parts[1] ?? '');
	$file = $root . '/modules/' . $module . '/src/' . str_replace('\\', '/', $relative) . '.php';
	if (is_file($file)) require_once $file;
});
foreach (['SmokeModuleRegistry', 'SmokeLinkTargetService', 'SmokeAssetResolver', 'SmokeAuthorizationService', 'SmokeSettingsStore'] as $fixture) {
	require_once __DIR__ . '/Support/' . $fixture . '.php';
}

use Base3\Api\IModuleRegistry;
use Base3\Core\ServiceLocator;
use Base3\Translation\Api\ITranslation;
use Base3\Translation\NoTranslation\NoTranslation;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3MoodleLab\Base3MoodleLabPlugin;
use Base3MoodleLab\Output\Base3MoodleGlobalChatbotLauncherOutput;
use Base3MoodleLab\Service\MoodleChatbotConfiguration;
use Base3MoodleLab\Test\Support\SmokeModuleRegistry;
use Base3MoodleLab\Test\Support\SmokeLinkTargetService;
use Base3MoodleLab\Test\Support\SmokeAssetResolver;
use Base3MoodleLab\Test\Support\SmokeAuthorizationService;
use Base3MoodleLab\Test\Support\SmokeSettingsStore;
use ClientStack\GlobalChatbotLauncher\GlobalChatbotLauncherFeature;
use Chatbot\SettingsTab\ChatbotconfigSettingsTab;

function verifyMoodleLauncher(bool $check, string $description): void {
	if (!$check) throw new RuntimeException('FAILED: ' . $description);
}
function get_config(string $component, string $key): mixed { return false; }

$translation = new NoTranslation();
$link = new SmokeLinkTargetService();
$container = new ServiceLocator();
$container->set(ITranslation::class, $translation);
$container->set(ILinkTargetService::class, $link);
$container->set(IModuleRegistry::class, new SmokeModuleRegistry());
(new Base3MoodleLabPlugin($container))->init();

$config = $container->get('chatbotConfigDisplayData');
verifyMoodleLauncher($config['group'] === MoodleChatbotConfiguration::GLOBAL_CHATBOT_GROUP
	&& $config['name'] === MoodleChatbotConfiguration::GLOBAL_CHATBOT_NAME, 'one project-owned chatbot identity');
$actions = ['list', 'stat', 'mkdir', 'delete', 'rmdir', 'copy', 'move', 'uploadStart', 'uploadChunk', 'uploadStatus', 'uploadFinish', 'uploadAbort', 'download'];
verifyMoodleLauncher(array_keys($config['resources']['endpoints']) === $actions, 'all native FileManager actions available');
foreach ($actions as $action) {
	parse_str((string)parse_url($config['resources']['endpoints'][$action], PHP_URL_QUERY), $params);
	verifyMoodleLauncher(($params['name'] ?? '') === 'base3moodlefilemanageroutput'
		&& ($params['fm_action'] ?? '') === $action, 'generic dispatch for ' . $action);
}

$authorization = new SmokeAuthorizationService(false);
$settings = new SmokeSettingsStore();
$launcher = new Base3MoodleGlobalChatbotLauncherOutput($link, new SmokeAssetResolver(), $translation, $settings, $authorization);
verifyMoodleLauncher($launcher->getOutput('html') === '', 'denied feature has no launcher');
$authorization->allowed = true;
$html = $launcher->getOutput('html');
verifyMoodleLauncher(str_contains($html, 'data-base3-global-chatbot-launcher')
	&& str_contains($html, 'data-dock-target="body"')
	&& str_contains($html, 'data-dock-header-target="nav.navbar.fixed-top"')
	&& !str_contains($html, 'data-dock-target="#page-wrapper"')
	&& str_contains($html, 'globalchatbotlauncher/index.js')
	&& !str_contains($html, 'bubble.js'), 'shared launcher docks the full Moodle document and adjusts the fixed header');
$feature = new GlobalChatbotLauncherFeature($translation);
verifyMoodleLauncher($feature->getDefaultAudience() === ['group' => 'authenticated']
	&& $feature::getName() === 'globalchatbotlauncherfeature', 'one shared launcher feature');
$tab = new ChatbotconfigSettingsTab($translation, $authorization, $config);
verifyMoodleLauncher($tab->getDisplayData() === $config, 'generic ChatbotconfigSettingsTab receives Moodle project configuration');
verifyMoodleLauncher(!is_file(__DIR__ . '/../src/Feature/GlobalChatbotLauncherFeature.php')
	&& !is_file(__DIR__ . '/../src/Service/Base3MoodleLabSettings.php'), 'obsolete duplicate owners removed');

echo "Moodle shared launcher and project settings smoke test passed.\n";
