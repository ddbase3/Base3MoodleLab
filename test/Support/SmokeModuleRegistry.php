<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab\Test\Support;

use Base3\CoreFoundation\Api\IModuleRegistry;

final class SmokeModuleRegistry implements IModuleRegistry {

	public function getModuleNames(): array { return []; }
	public function getModulePath(string $name): ?string { return null; }
	public function requireModulePath(string $name): string { throw new \RuntimeException('Unknown module: ' . $name); }
}
