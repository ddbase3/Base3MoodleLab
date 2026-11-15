<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab;

use Base3\CoreFoundation\Accesscontrol\Api\IAccesscontrol;
use Base3\CoreFoundation\Api\IBase;
use Base3\CoreFoundation\Api\IClassMap;
use Base3\CoreFoundation\Api\IContainer;
use Base3\CoreFoundation\Api\IModuleRegistry;
use Base3\CoreFoundation\Api\IPlugin;
use Base3\CoreFoundation\Database\Api\IDatabase;
use Base3\CoreFoundation\LinkTarget\Api\ILinkTargetService;
use Base3\CoreFoundation\Migration\Api\IMigrationRunner;
use Base3\Core\Migration\Database\DatabaseMigrationRunner;
use Base3\CoreFoundation\Session\Api\ISession;
use Base3\CoreFoundation\Settings\Api\ISettingsStore;
use Base3\Core\Settings\Database\DatabaseSettingsStore;
use Base3\CoreFoundation\State\Api\IStateStore;
use Base3\Core\State\Database\DatabaseStateStore;
use Base3\CoreFoundation\Translation\Api\ITranslation;
use Base3\Base3MoodleLab\Service\MoodleChatbotConfiguration;

final class Base3MoodleLabPlugin implements IPlugin, IBase {

	public function __construct(private readonly IContainer $container) {}
	public static function getName(): string { return 'base3moodlelabplugin'; }

	public function init(): void {
		$this->container
			->set(self::getName(), $this, IContainer::SHARED)
			->set(ISettingsStore::class, fn($c) => new DatabaseSettingsStore($c->get(IDatabase::class)), IContainer::SHARED)
			->set(IStateStore::class, fn($c) => new DatabaseStateStore($c->get(IDatabase::class)), IContainer::SHARED)
			->set(IMigrationRunner::class, fn($c) => new DatabaseMigrationRunner($c->get(IClassMap::class), $c->get(IDatabase::class)), IContainer::SHARED)
			->set(MoodleChatbotConfiguration::class, fn($c) => new MoodleChatbotConfiguration(
				$c->get(ITranslation::class), $c->get(ILinkTargetService::class)
			), IContainer::SHARED)
			->set('chatbotConfigDisplayData', fn($c) => $c->get(MoodleChatbotConfiguration::class)->getDisplayData(), IContainer::SHARED);

		// Optional MissionBay composition is a deliberate project-level decision.
		if ($this->container->get(IModuleRegistry::class)->getModulePath('MissionBay') !== null) {
			$this->container
				->set(\Base3\AssistantFoundation\Api\IAgentRuntimeSelector::class, fn($c) => new \Base3\AssistantRuntime\Service\PreferredAgentRuntimeSelector(
					$c->get(\Base3\AssistantFoundation\Api\IAgentRuntimeRegistry::class), 'missionbay'
				), IContainer::SHARED)
				->set(\Base3\MissionBay\Api\IAgentKnowledgeService::class, fn($c) => new \Base3\MissionBay\Agent\GlobalAgentKnowledgeService(
					$c->get(IDatabase::class), $c->get(IAccesscontrol::class), $c->get(ISession::class)
				), IContainer::SHARED);
		}
	}
}
