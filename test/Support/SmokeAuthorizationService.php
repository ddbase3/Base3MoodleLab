<?php declare(strict_types=1);

namespace Base3\Base3MoodleLab\Test\Support;

use Base3\CoreFoundation\Authorization\Api\IAuthorizationService;
use Base3\CoreFoundation\Authorization\Dto\AuthorizationDecision;
use Base3\CoreFoundation\Authorization\Dto\AuthorizationRequest;

final class SmokeAuthorizationService implements IAuthorizationService {

	public function __construct(public bool $allowed = false) {}

	public function getDefaultAuthority(): string { return 'moodle'; }

	public function decide(AuthorizationRequest $request): AuthorizationDecision {
		if ($this->allowed && $request->permission->scope === 'feature' && $request->permission->permission === 'globalchatbotlauncherfeature') {
			return AuthorizationDecision::allow();
		}
		return AuthorizationDecision::deny();
	}

	public function decideMany(array $requests): array {
		$result = [];
		foreach ($requests as $key => $request) $result[$key] = $this->decide($request);
		return $result;
	}
}
