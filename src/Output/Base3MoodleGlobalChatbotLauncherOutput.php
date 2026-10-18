<?php declare(strict_types=1);

namespace Base3MoodleLab\Output;

use Base3\Api\IAssetResolver;
use Base3\Api\IOutput;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3\Settings\Api\ISettingsStore;
use Base3\Translation\Api\ITranslation;
use Base3MoodleLab\Service\Base3MoodleLabSettings;
use ClientStack\GlobalChatbotLauncher\GlobalChatbotLauncherSettings;

/** Moodle-specific HTML for the platform-independent ClientStack launcher. */
final class Base3MoodleGlobalChatbotLauncherOutput implements IOutput {

	public function __construct(
		private readonly ILinkTargetService $linkTargetService,
		private readonly IAssetResolver $assetResolver,
		private readonly ITranslation $translation,
		private readonly ISettingsStore $settingsStore
	) {}

	public static function getName(): string {
		return 'base3moodleglobalchatbotlauncheroutput';
	}

	public function getOutput(string $out = 'html', bool $final = false): string {
		if ($out !== 'html') return '';

		$identity = [
			'config_group' => Base3MoodleLabSettings::GLOBAL_CHATBOT_GROUP,
			'config_name' => Base3MoodleLabSettings::GLOBAL_CHATBOT_NAME
		];
		$data = rtrim(strtr(base64_encode(json_encode($identity, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
		$url = $this->linkTargetService->getLink(
			['name' => 'chatbotdisplay', 'out' => 'html'],
			['base3_display_data' => $data]
		);
		$configuration = $this->getLauncherConfiguration();
		$label = $this->t('global_chatbot_label', 'Chat');
		$attributes = [
			'data-ajax-url' => $url,
			'data-dock-target' => '#page-wrapper',
			'data-dock-header-target' => 'nav.navbar.fixed-top',
			'data-button-label' => $label,
			'data-header-title' => $this->t('global_chatbot_header_title', $label),
			'data-config-group' => $identity['config_group'],
			'data-config-name' => $identity['config_name'],
			'data-close-label' => $this->t('global_chatbot_close', 'Close chat'),
			'data-loading-label' => $this->t('global_chatbot_loading', 'Loading chat ...'),
			'data-load-error' => $this->t('global_chatbot_load_error', 'The chatbot could not be loaded.'),
			'data-resize-label' => $this->t('global_chatbot_resize', 'Resize chat window'),
			'data-mode-overlay-label' => $this->t('global_chatbot_mode_overlay', 'Floating window'),
			'data-mode-modal-label' => $this->t('global_chatbot_mode_modal', 'Dialog'),
			'data-mode-docked-label' => $this->t('global_chatbot_mode_docked', 'Sidebar'),
			'data-mode-cover-label' => $this->t('global_chatbot_mode_cover', 'Full window'),
			'data-mode-fullscreen-label' => $this->t('global_chatbot_mode_fullscreen', 'Fullscreen')
		];
		foreach ($configuration as $name => $enabled) {
			$attributes['data-' . str_replace('_', '-', $name)] = $enabled ? '1' : '0';
		}

		$markup = '<div id="base3-chatbot-config" data-base3-global-chatbot-launcher hidden';
		foreach ($attributes as $name => $value) {
			$markup .= "\n\t" . $name . '="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
		}
		$markup .= '></div>';

		$css = $this->assetResolver->resolve('plugin/ClientStack/assets/globalchatbotlauncher/styles/globalchatbotlauncher.css');
		$module = $this->assetResolver->resolve('plugin/ClientStack/assets/globalchatbotlauncher/index.js');
		return '<link rel="stylesheet" href="' . htmlspecialchars($css, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . "\n"
			. $markup . "\n"
			. '<script type="module" src="' . htmlspecialchars($module, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"></script>';
	}

	/** Import the former native Moodle window options once, then read only BASE3 settings. */
	private function getLauncherConfiguration(): array {
		if (!$this->settingsStore->has(GlobalChatbotLauncherSettings::GROUP, GlobalChatbotLauncherSettings::NAME)) {
			$legacy = [
				'mode_modal_enabled' => 'modal',
				'mode_docked_enabled' => 'docked',
				'mode_cover_enabled' => 'cover',
				'mode_fullscreen_enabled' => 'fullscreen',
				'resizable_overlay' => 'resizeoverlay',
				'resizable_modal' => 'resizemodal',
				'resizable_docked' => 'resizedocked',
				'remember_last_mode' => 'remembermode',
				'reopen_overlay' => 'reopenoverlay',
				'reopen_docked' => 'reopendocked'
			];
			$values = [];
			foreach ($legacy as $name => $legacyKey) {
				$value = get_config('local_base3', 'chatbotbubble_' . $legacyKey);
				if ($value !== false && $value !== null) $values[$name] = (string) $value === '1';
			}
			if ($values !== []) GlobalChatbotLauncherSettings::save($this->settingsStore, $values);
		}
		return GlobalChatbotLauncherSettings::get($this->settingsStore);
	}

	private function t(string $key, string $fallback): string {
		return $this->translation->translate('Administration', 'administration', $key, $fallback);
	}
}
