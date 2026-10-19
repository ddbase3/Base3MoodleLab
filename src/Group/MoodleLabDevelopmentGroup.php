<?php declare(strict_types=1);

namespace Base3MoodleLab\Group;

use Base3\Usermanager\Api\IGroup;
use Base3\Usermanager\Group;

/** Optional project-specific, immutable bootstrap membership supplemented by editable criteria. */
final class MoodleLabDevelopmentGroup implements IGroup {

	/** @var list<string> Explicit, exact Moodle login names; configure before restricting the administration UI. */
	private const BOOTSTRAP_USERNAMES = ['admin'];

	/** @var list<string> Verified, complete email addresses only; prefer login names. */
	private const BOOTSTRAP_EMAILS = [];

	public static function getName(): string { return 'moodlelabdevelopmentgroup'; }
	public function getGroup(): Group { return Group::fromArray(['id' => 'development', 'name' => 'development', 'info' => 'BASE3 developers and project-defined maintainers', 'roles' => []]); }
	public function getCriteria(): array {
		return ['criterion' => 'base3useridentitycriterion', 'config' => [
			'usernames' => self::BOOTSTRAP_USERNAMES, 'emails' => self::BOOTSTRAP_EMAILS
		]];
	}
	public function isEditable(): bool { return true; }
}
