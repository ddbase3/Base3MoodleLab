<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab\Test\Support;

use Base3\CoreFoundation\Settings\Api\ISettingsStore;

final class SmokeSettingsStore implements ISettingsStore {

	private array $entries = [];

	public function get(string $group, string $name, array $default = []): array { return $this->entries[$group][$name] ?? $default; }
	public function set(string $group, string $name, array $settings): void { $this->entries[$group][$name] = $settings; }
	public function has(string $group, string $name): bool { return isset($this->entries[$group][$name]); }
	public function remove(string $group, string $name): void { unset($this->entries[$group][$name]); }
	public function getGroup(string $group): array { return $this->entries[$group] ?? []; }
	public function save(): void {}
	public function reload(): void {}
}
