<?php declare(strict_types=1);

namespace Base3MoodleLab\Service;

use Base3\LinkTarget\Api\ILinkTargetService;
use Base3\Translation\Api\ITranslation;

/** Owns the Moodle project's chatbot/default display configuration. */
final class MoodleChatbotConfiguration {

	public const GLOBAL_CHATBOT_GROUP = 'chatbot';
	public const GLOBAL_CHATBOT_NAME = 'default';

	public function __construct(
		private readonly ITranslation $translation,
		private readonly ILinkTargetService $linkTargetService
	) {}

	public function getDisplayData(): array {
		$endpoints = [];
		foreach (['list', 'stat', 'mkdir', 'delete', 'rmdir', 'copy', 'move', 'uploadStart', 'uploadChunk', 'uploadStatus', 'uploadFinish', 'uploadAbort', 'download'] as $action) {
			$endpoints[$action] = $this->linkTargetService->getLink(
				['name' => 'base3moodlefilemanageroutput', 'out' => 'json'],
				['fm_action' => $action]
			);
		}
		return [
			'group' => self::GLOBAL_CHATBOT_GROUP,
			'name' => self::GLOBAL_CHATBOT_NAME,
			'title' => $this->t('chatbot_config_title', 'Chatbot Configuration'),
			'description' => $this->t('moodle_chatbot_config_description', 'Global configuration for the Moodle chatbot.'),
			'submit_label' => $this->t('save', 'Save'),
			'mode' => 'standalone',
			'save_mode' => 'ajax',
			'resources' => [
				'enabled' => true,
				'endpoints' => $endpoints,
				'max_file_size' => 50 * 1024 * 1024,
				'accept' => 'application/pdf,.pdf'
			]
		];
	}

	private function t(string $key, string $fallback): string {
		return $this->translation->translate('Administration', 'administration', $key, $fallback);
	}
}
