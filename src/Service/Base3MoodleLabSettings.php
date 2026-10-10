<?php declare(strict_types=1);

namespace Base3MoodleLab\Service;

use Base3\Translation\Api\ITranslation;
use Base3Moodle\Api\IBase3MoodleSettings;

/**
 * Moodle equivalent of Base3IliasLabSettings.
 * Only configured displays which are actually installed are rendered by
 * ClientStack's existing TabControlDisplay.
 */
final class Base3MoodleLabSettings implements IBase3MoodleSettings {

	public function __construct(private readonly ITranslation $translation) {}

	public function getAdministrationConfig(): array {
		return [
			$this->tab('base3', 'BASE3', [
				['logadmindisplay', 'BASE3 Log'],
				['servicesadmindisplay', 'Services'],
				['configurationadmindisplay', 'Configuration'],
				['usermanagerdebugdisplay', 'User Manager'],
				['statestoreadmindisplay', 'State Store'],
				['jobsadmindisplay', 'Jobs'],
				['databaseworkbenchdisplay', 'Database workbench']
			]),
			$this->tab('chatbot', 'Chatbot', [
				['chatbotconfigdisplay', 'Configuration', ['data' => $this->chatbotConfig()]],
				['chatbotextensionsconfigdisplay', 'Extensions']
			]),
			$this->tab('provider', 'Provider', [
				['connectionconfigdisplay', 'Connections'],
				['llmconfigdisplay', 'LLMs'],
				['embeddingconfigdisplay', 'Embeddings'],
				['speechtotextconfigdisplay', 'Speech-to-Text'],
				['texttospeechconfigdisplay', 'Text-to-Speech'],
				['imageconfigdisplay', 'Images'],
				['searchconfigdisplay', 'Search'],
				['parserserviceconfigdisplay', 'Parser Services'],
				['vectorstoreconfigdisplay', 'Vector Stores']
			]),
			$this->tab('aiusage', 'AI Usage', [
				['ai_usage_overview', 'Overview', [
					'display' => 'compositedisplay',
					'data' => [
						'columns' => 2,
						'items' => [
							['id' => 'metrics', 'display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_metrics', 'span' => 2],
							['id' => 'tokens', 'display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_tokens_timeline'],
							['id' => 'operations', 'display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_operations'],
							['id' => 'models', 'display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_models', 'span' => 2]
						]
					]
				]],
				['ai_usage_tokens', 'Tokens', ['display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_tokens_timeline']],
				['ai_usage_models', 'Models', ['display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_models']],
				['ai_usage_operations', 'Operations', ['display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_operations']],
				['ai_usage_requests', 'Requests', ['display' => 'generalreportdisplay', 'data' => 'missionbay_ai_usage_requests']]
			]),
			$this->tab('agents', 'Agents', [
				['agentadmindisplay', 'Agents'],
				['agentcompositionadmindisplay', 'Effective Composition'],
				['agentorchestratorprofileadmindisplay', 'Orchestrator Profiles'],
				['toolprofileadmindisplay', 'Tool Profiles'],
				['agentmemoryprofileadmindisplay', 'Memory Profiles'],
				['agentcontextprofileadmindisplay', 'Context Profiles'],
				['agentcomponentpresetadmindisplay', 'Component Presets']
			]),
			$this->tab('agenttools', 'Agent Tools', [
				['agenttoollogadmindisplay', 'Tool Log'],
				['agentcomponentpresettestadmindisplay', 'Component Preset Tests'],
				['agentinfotopicprovidertestadmindisplay', 'InfoTopicProvider Test'],
				['knowledgeagentmemoryadmindisplay', 'Knowledge / Skills'],
				['userprefdefadmindisplay', 'User Preferences']
			]),
			$this->tab('embedding', 'Embedding', [
				['embeddingorchestratorconfigadmindisplay', 'Configuration'],
				['retrievalcollectionadmindisplay', 'Collections'],
				['retrievalvectorpointsadmindisplay', 'Vector Points'],
				['retrievalsearchadmindisplay', 'Retrieval Search']
			]),
			$this->tab('parseflow', 'ParseFlow', [
				['parserexploreradmindisplay', 'Routes'],
				['parseflowparseradmindisplay', 'Parsers']
			]),
			$this->tab('contentforge', 'ContentForge', [
				['contentforgestepwidgetdisplay', 'ContentForge']
			]),
			$this->tab('datahawk', 'DataHawk', [
				['datahawkschemadisplay', 'DB Schema'],
				['datahawkmaterializationoverviewdisplay', 'Materialization'],
				['datahawkmaterializationmanifestdisplay', 'Manifests'],
				['datahawkmaterializationregistrydisplay', 'Registry'],
				['datahawkmaterializationrundisplay', 'Runs'],
				['datahawkmaterializationtabledisplay', 'Tables']
			]),
			$this->tab('credentials', 'API Keys', [
				['keymanagementdisplay', 'My API Keys'],
				['keyharboradmindisplay', 'Administration'],
				['keyharbordemoadmindisplay', 'Demo']
			]),
			$this->tab('messaging', 'Messaging', [
				['messagehubdashboarddisplay', 'Dashboard'],
				['messagetemplateadmindisplay', 'Templates'],
				['messagevariantadmindisplay', 'Variants'],
				['messagetypesyncadmindisplay', 'Type Sync'],
				['messagequeueadmindisplay', 'Queue'],
				['messagedeliverylogadmindisplay', 'Deliveries'],
				['messagetransportadmindisplay', 'Transports'],
				['messagehubdemoadmindisplay', 'Demo']
			])
		];
	}

	/** @param array<int,array{0:string,1:string,2?:array<string,mixed>}> $entries */
	private function tab(string $name, string $label, array $entries): array {
		$displays = [];
		foreach ($entries as $entry) {
			$display = [
				'name' => $entry[0],
				'label' => $this->t('base3_admin_subtab_' . $entry[0], $entry[1])
			];
			$displays[] = array_replace($display, $entry[2] ?? []);
		}
		return [
			'name' => $name,
			'label' => $this->t('base3_admin_tab_' . $name, $label),
			'displays' => $displays
		];
	}

	private function chatbotConfig(): array {
		$baseUrl = (new \moodle_url('/local/base3/filemanager.php', ['sesskey' => sesskey()]))->out(false);
		$endpoints = [];
		foreach (['list', 'stat', 'mkdir', 'delete', 'rmdir', 'copy', 'move', 'uploadStart', 'uploadChunk', 'uploadStatus', 'uploadFinish', 'uploadAbort', 'download'] as $action) {
			$endpoints[$action] = (new \moodle_url($baseUrl, ['fm_action' => $action]))->out(false);
		}
		return [
			'group' => 'chatbot',
			'name' => 'default',
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
