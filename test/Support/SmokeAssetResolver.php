<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab\Test\Support;

use Base3\CoreFoundation\Api\IAssetResolver;

final class SmokeAssetResolver implements IAssetResolver {

	public function resolve(string $path): string { return '/assets/' . $path; }
}
