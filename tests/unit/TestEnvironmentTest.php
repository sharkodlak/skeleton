<?php

declare(strict_types = 1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards the safety net in tests/bootstrap.php.
 *
 * If this fails, the suite is pointed at the development database and any test
 * that writes will destroy real data, so it is worth asserting outright rather
 * than discovering it after the fact.
 */
final class TestEnvironmentTest extends TestCase {
	public function testDatabaseNameIsRewrittenToTheTestDatabase(): void {
		self::assertStringEndsWith('_test', (string) \getenv('DB_NAME'));
	}

	public function testRewrittenDatabaseNameIsVisibleToTheApplicationBootstrap(): void {
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		self::assertSame(\getenv('DB_NAME'), $_ENV['DB_NAME'] ?? null);
	}
}
