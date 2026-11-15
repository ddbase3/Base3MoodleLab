<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab\Test\Support;

use Base3\CoreFoundation\LinkTarget\Api\ILinkTargetService;

final class SmokeLinkTargetService implements ILinkTargetService {

	public function getLink(array $target, array $params = []): string {
		return '/local/base3/ajax.php?' . http_build_query(array_merge($params, $target, ['sesskey' => 'test-session']));
	}
}
