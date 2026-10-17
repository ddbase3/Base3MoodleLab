<?php declare(strict_types=1);

namespace Base3MoodleLab;

use Base3\Accesscontrol\Api\IAccesscontrol;
use Base3\Api\IBase;
use Base3\Api\IClassMap;
use Base3\Api\IContainer;
use Base3\Api\IModuleRegistry;
use Base3\Api\IPlugin;
use Base3\Database\Api\IDatabase;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3\Migration\Api\IMigrationRunner;
use Base3\Migration\Database\DatabaseMigrationRunner;
use Base3\Session\Api\ISession;
use Base3\Settings\Api\ISettingsStore;
use Base3\Settings\Database\DatabaseSettingsStore;
use Base3\State\Api\IStateStore;
use Base3\State\Database\DatabaseStateStore;
use Base3\Translation\Api\ITranslation;
use Base3Moodle\Api\IBase3MoodleSettings;
use Base3MoodleLab\Service\Base3MoodleLabSettings;

final class Base3MoodleLabPlugin implements IPlugin, IBase {

	public function __construct(private readonly IContainer $container) {}
	public static function getName(): string { return 'base3moodlelabplugin'; }

	public function init(): void {
		$this->container
			->set(self::getName(), $this, IContainer::SHARED)
			->set(ISettingsStore::class, fn($c) => new DatabaseSettingsStore($c->get(IDatabase::class)), IContainer::SHARED)
			->set(IStateStore::class, fn($c) => new DatabaseStateStore($c->get(IDatabase::class)), IContainer::SHARED)
			->set(IMigrationRunner::class, fn($c) => new DatabaseMigrationRunner($c->get(IClassMap::class), $c->get(IDatabase::class)), IContainer::SHARED)
			->set(IBase3MoodleSettings::class, fn($c) => new Base3MoodleLabSettings($c->get(ITranslation::class), $c->get(ILinkTargetService::class)), IContainer::SHARED);

		// Optional MissionBay composition is a deliberate project-level decision.
		if ($this->container->get(IModuleRegistry::class)->getModulePath('MissionBay') !== null) {
			$this->container
				->set(\AssistantFoundation\Api\IAgentRuntimeSelector::class, fn($c) => new \AssistantRuntime\Service\PreferredAgentRuntimeSelector(
					$c->get(\AssistantFoundation\Api\IAgentRuntimeRegistry::class), 'missionbay'
				), IContainer::SHARED)
				->set(\MissionBay\Api\IAgentKnowledgeService::class, fn($c) => new \MissionBay\Agent\GlobalAgentKnowledgeService(
					$c->get(IDatabase::class), $c->get(IAccesscontrol::class), $c->get(ISession::class)
				), IContainer::SHARED);
		}
	}
}
