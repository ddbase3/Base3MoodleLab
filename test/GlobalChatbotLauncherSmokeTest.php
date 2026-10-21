<?php declare(strict_types=1);

// Run after updating ClientStack: php modules/Base3MoodleLab/test/GlobalChatbotLauncherSmokeTest.php
// Standalone rendering, configuration, legacy migration and visibility regression test.

namespace Base3\Api {
	interface IBase { public static function getName(): string; }
	interface IOutput extends IBase { public function getOutput(string $out = 'html', bool $final = false): string; }
	interface IAssetResolver { public function resolve(string $path): string; }
}

namespace Base3\Usermanager {
	final class Permission {
		public function __construct(public readonly string $scope, public readonly string $permission) {}
		public static function for(string $scope, string $permission): self { return new self($scope, $permission); }
	}
}

namespace Base3\Authorization\Dto {
	final class AuthorizationRequest {
		public function __construct(public readonly string $authority, public readonly \Base3\Usermanager\Permission $permission) {}
	}
	final class AuthorizationDecision {
		public function __construct(private readonly bool $allowed) {}
		public function isAllowed(): bool { return $this->allowed; }
	}
}

namespace Base3\Authorization\Api {
	interface IFeature extends \Base3\Api\IBase {
		public function getLabel(): string;
		public function getDescription(): string;
		public function getPermissions(): array;
		public function getDefaultAudience(): array;
	}
	interface IAuthorizationService {
		public function getDefaultAuthority(): string;
		public function decide(\Base3\Authorization\Dto\AuthorizationRequest $request): \Base3\Authorization\Dto\AuthorizationDecision;
		public function decideMany(array $requests): array;
	}
}

namespace local_base3 {
	final class Runtime {
		public static function boot(): void {}
		public static function container(): \TestContainer { return new \TestContainer(); }
	}
}

namespace Base3\LinkTarget\Api {
	interface ILinkTargetService { public function getLink(array $target, array $params = []): string; }
}

namespace Base3\Settings\Api {
	interface ISettingsStore {
		public function get(string $group, string $name, array $default = []): array;
		public function set(string $group, string $name, array $settings): void;
		public function has(string $group, string $name): bool;
		public function remove(string $group, string $name): void;
		public function getGroup(string $group): array;
		public function save(): void;
		public function reload(): void;
	}
}

namespace Base3\Translation\Api {
	interface ITranslation {
		public function translate(string $set, string $section, string $key, string $fallback = '', array $replacements = []): string;
	}
}

namespace Base3Moodle\Api {
	interface IBase3MoodleSettings {
		public function getAdministrationConfig(): array;
		public function getVisibleAdministrationConfig(): array;
	}
}

namespace Base3\Usermanager\Api {
	interface IUsermanager { public function getGroups(); }
}

namespace {
	use Base3\Authorization\Api\IAuthorizationService;
	use Base3\Authorization\Dto\AuthorizationDecision;
	use Base3\Authorization\Dto\AuthorizationRequest;
	use Base3\Api\IAssetResolver;
	use Base3\LinkTarget\Api\ILinkTargetService;
	use Base3\Settings\Api\ISettingsStore;
	use Base3\Translation\Api\ITranslation;
	use Base3\Usermanager\Api\IUsermanager;
	use Base3MoodleLab\Feature\GlobalChatbotLauncherFeature;
	use Base3MoodleLab\Output\Base3MoodleGlobalChatbotLauncherOutput;
	use Base3MoodleLab\Service\Base3MoodleLabSettings;
	use ClientStack\GlobalChatbotLauncher\GlobalChatbotLauncherSettings;
	use local_base3\ChatbotBubble;

	final class TestContainer {
		public function get(string $id): object {
			if ($id !== IAuthorizationService::class) throw new \RuntimeException('Unexpected service: ' . $id);
			return $GLOBALS['authorization'];
		}
	}

	final class TestAuthorization implements IAuthorizationService {
		public int $decisions = 0;
		public function getDefaultAuthority(): string { return 'moodle'; }
		public function decide(AuthorizationRequest $request): AuthorizationDecision {
			$this->decisions++;
			verify($request->authority === 'moodle'
				&& $request->permission->scope === 'feature'
				&& $request->permission->permission === GlobalChatbotLauncherFeature::getName(),
				'floating launcher uses only its declared BASE3 feature permission');
			return new AuthorizationDecision((bool)($GLOBALS['featureAllowed'] ?? false));
		}
		public function decideMany(array $requests): array {
			return array_map(fn(AuthorizationRequest $request): AuthorizationDecision => $this->decide($request), $requests);
		}
	}

	$nativeSettings = [];
	function get_config(string $component, string $name): mixed {
		global $nativeSettings;
		return $nativeSettings[$component][$name] ?? false;
	}
	function isloggedin(): bool { return $GLOBALS['loggedIn'] ?? false; }
	function isguestuser(): bool { return $GLOBALS['guestUser'] ?? false; }
	function has_capability(string $capability, object $context): bool {
		return $capability === 'moodle/site:config' && ($GLOBALS['siteAdmin'] ?? false);
	}
	class context_system { public static function instance(): object { return new self(); } }
	class moodle_url {
		public function __construct(private string $path, private array $params = []) {}
		public function out(bool $escaped = true): string { return $this->path . ($this->params !== [] ? '?' . http_build_query($this->params) : ''); }
	}
	function sesskey(): string { return 'moodle-session-key'; }

	final class TestSettingsStore implements ISettingsStore {
		private array $groups = [];
		public int $saves = 0;
		public function get(string $group, string $name, array $default = []): array { return $this->groups[$group][$name] ?? $default; }
		public function set(string $group, string $name, array $settings): void { $this->groups[$group][$name] = $settings; }
		public function has(string $group, string $name): bool { return isset($this->groups[$group][$name]); }
		public function remove(string $group, string $name): void { unset($this->groups[$group][$name]); }
		public function getGroup(string $group): array { return $this->groups[$group] ?? []; }
		public function save(): void { $this->saves++; }
		public function reload(): void {}
	}

	function verify(bool $condition, string $message): void {
		if (!$condition) throw new \RuntimeException('FAILED: ' . $message);
	}

	$clientStackSettings = dirname(__DIR__, 2) . '/ClientStack/src/GlobalChatbotLauncher/GlobalChatbotLauncherSettings.php';
	if (!is_file($clientStackSettings)) throw new \RuntimeException('Pull ClientStack before running the Moodle launcher smoke test.');
	require_once $clientStackSettings;
	require_once __DIR__ . '/../src/Service/Base3MoodleLabSettings.php';
	require_once __DIR__ . '/../src/Feature/GlobalChatbotLauncherFeature.php';
	require_once __DIR__ . '/../src/Output/Base3MoodleGlobalChatbotLauncherOutput.php';
	require_once __DIR__ . '/../../../moodle/local/base3/classes/ChatbotBubble.php';

	$link = new class implements ILinkTargetService {
		public function getLink(array $target, array $params = []): string {
			return '/local/base3/ajax.php?' . http_build_query(array_merge($params, [
				'name' => (string) ($target['name'] ?? ''),
				'out' => (string) ($target['out'] ?? 'html'),
				'sesskey' => sesskey()
			]));
		}
	};
	$resolver = new class implements IAssetResolver {
		public function resolve(string $path): string { return '/local/base3/assets/' . str_replace('plugin/', '', $path) . '?v=123456789abc'; }
	};
	$translation = new class implements ITranslation {
		public function translate(string $set, string $section, string $key, string $fallback = '', array $replacements = []): string {
			return $key === 'global_chatbot_header_title' ? 'Ask <strong>KIM</strong>' : $fallback;
		}
	};

	$feature = new GlobalChatbotLauncherFeature($translation);
	verify($feature::getName() === 'globalchatbotlauncherfeature'
		&& $feature->getDefaultAudience() === ['group' => 'authenticated']
		&& count($feature->getPermissions()) === 1,
		'new launcher feature is discoverable with authenticated default audience');
	$authorization = new TestAuthorization();
	$GLOBALS['authorization'] = $authorization;
	$nativeSettings['local_base3'] = [
		'chatbotbubble_enabled' => '0', 'chatbotbubble_visibility' => 'administrators',
		'chatbotbubble_modal' => '1', 'chatbotbubble_docked' => '1',
		'chatbotbubble_resizeoverlay' => '1', 'chatbotbubble_reopendocked' => '1'
	];
	$GLOBALS['featureAllowed'] = false;
	verify(!ChatbotBubble::visible(), 'denied feature hides the global launcher');
	$GLOBALS['featureAllowed'] = true;
	verify(ChatbotBubble::visible(), 'allowed feature shows launcher despite obsolete Moodle visibility flags');

	$store = new TestSettingsStore();
	$output = new Base3MoodleGlobalChatbotLauncherOutput($link, $resolver, $translation, $store, $authorization);
	$GLOBALS['featureAllowed'] = false;
	verify($output->getOutput('html') === '' && $store->saves === 0,
		'direct output invocation also refuses rendering for a denied user');
	$GLOBALS['featureAllowed'] = true;
	verify(Base3MoodleGlobalChatbotLauncherOutput::getName() === strtolower((new \ReflectionClass($output))->getShortName()), 'BASE3 classmap name is lowercase class name');
	$html = $output->getOutput('html');
	verify(str_contains($html, 'data-base3-global-chatbot-launcher')
		&& str_contains($html, 'ClientStack/assets/globalchatbotlauncher/index.js')
		&& str_contains($html, 'ClientStack/assets/globalchatbotlauncher/styles/globalchatbotlauncher.css')
		&& !str_contains($html, 'bubble.js') && !str_contains($html, 'bubble.css') && !str_contains($html, '<iframe'),
		'shared ClientStack module replaces legacy standalone Moodle launcher');
	verify(str_contains($html, 'data-dock-target="#page-wrapper"')
		&& str_contains($html, 'data-dock-header-target="nav.navbar.fixed-top"'), 'Moodle sidebar and fixed header docking use the existing ClientStack API');
	verify(str_contains($html, 'data-header-title="Ask &lt;strong&gt;KIM&lt;/strong&gt;"'), 'HTML header title is safely attribute-escaped for ClientStack rendering');
	verify(str_contains($html, 'data-button-label="Chat"')
		&& !str_contains($html, 'data-launcher-icon='),
		'Moodle uses the default blue labeled ClientStack launcher button, identical to WordPress');
	verify(str_contains($html, 'data-mode-docked-enabled="1"')
		&& str_contains($html, 'data-resizable-overlay="1"')
		&& str_contains($html, 'data-reopen-docked="1"'), 'legacy window settings are imported');
	verify($store->saves === 1, 'legacy window options are saved to canonical BASE3 settings once');
	$output->getOutput('html');
	verify($store->saves === 1, 'subsequent visits do not repeat import');

	verify(preg_match('/data-ajax-url="([^"]+)"/', $html, $match) === 1, 'generic BASE3 display URL is supplied');
	parse_str((string) parse_url(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'), PHP_URL_QUERY), $query);
	verify(($query['name'] ?? '') === 'chatbotdisplay' && ($query['out'] ?? '') === 'html'
		&& ($query['sesskey'] ?? '') === sesskey(), 'Moodle generic dispatcher and sesskey are retained');
	$encoded = (string) ($query['base3_display_data'] ?? '');
	$decoded = base64_decode(strtr(str_pad($encoded, strlen($encoded) + (4 - strlen($encoded) % 4) % 4, '='), '-_', '+/'), true);
	verify(json_decode((string) $decoded, true, 512, JSON_THROW_ON_ERROR)
		=== ['config_group' => 'chatbot', 'config_name' => 'default'], 'global instance matches BASE3 chatbot administration');

	$canonical = GlobalChatbotLauncherSettings::DEFAULTS;
	$canonical['mode_docked_enabled'] = false;
	GlobalChatbotLauncherSettings::save($store, $canonical);
	$precedence = $output->getOutput('html');
	verify(str_contains($precedence, 'data-mode-docked-enabled="0"')
		&& $store->saves === 2, 'central BASE3 settings take precedence over old Moodle settings');

	$users = new class implements IUsermanager {
		public function getGroups() { return []; }
	};
	$lab = new Base3MoodleLabSettings($translation, $link, $users);
	$configuration = $lab->getAdministrationConfig();
	$tabs = [];
	foreach ($configuration as $tab) if (($tab['name'] ?? '') === 'chatbot') $tabs = $tab['displays'];
	$base3Displays = [];
	foreach ($configuration as $tab) if (($tab['name'] ?? '') === 'base3') $base3Displays = array_column($tab['displays'], 'name');
	verify(in_array('authorizationgroupsdisplay', $base3Displays, true)
		&& in_array('authorizationfeaturesdisplay', $base3Displays, true)
		&& in_array('authorizationadministrationdisplay', $base3Displays, true)
		&& !in_array('moodlegroupadmindisplay', $base3Displays, true)
		&& !in_array('moodlefeatureadmindisplay', $base3Displays, true),
		'BASE3 administration uses exclusively shared ClientStack authorization displays');
	$reduced = $lab->getVisibleAdministrationConfig();
	verify(array_column($reduced, 'name') === ['base3', 'games'], 'non-developer sees reduced administration and games');
	verify(array_column($reduced[0]['displays'], 'name') === ['authorizationadministrationdisplay'],
		'Group and feature administration are not visible without development membership');
	verify(array_column($tabs, 'name') === [
		'chatbotconfigdisplay', 'globalchatbotlauncherconfigdisplay', 'chatbotextensionsconfigdisplay'
	], 'BASE3 chatbot tabs are Configuration / Launcher / Extensions');
	verify(($tabs[0]['data']['group'] ?? '') === 'chatbot'
		&& ($tabs[0]['data']['name'] ?? '') === 'default'
		&& count($tabs[0]['data']['resources']['endpoints'] ?? []) === 13, 'original chatbot config and all FileManager actions are unchanged');
	verify(str_contains(file_get_contents(__DIR__ . '/../../../moodle/local/base3/classes/hook_callbacks.php'), "Runtime::renderEmbedded('base3moodleglobalchatbotlauncheroutput')"), 'native footer hook calls discoverable launcher through existing BASE3 runtime');
	verify(!str_contains(file_get_contents(__DIR__ . '/../../../moodle/local/base3/settings.php'), 'chatbotbubble_visibility')
		&& !str_contains(file_get_contents(__DIR__ . '/../../../moodle/local/base3/settings.php'), 'chatbotbubble_enabled'),
		'native Moodle launcher visibility settings have been removed');

	echo "GlobalChatbotLauncherSmokeTest OK: shared launcher, feature authorization, settings migration, docking, admin tabs and generic dispatch.\n";
}
