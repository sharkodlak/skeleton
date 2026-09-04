<?php

declare(strict_types = 1);

namespace Tests\Integration;

/**
 * Proves the test database exists, is reachable and is not the development one.
 *
 * Also exercises IntegrationTestCase itself, so a project inheriting from it
 * finds out here — not in its own first integration test — if the database was
 * never created or migrated.
 */
final class TestDatabaseTest extends IntegrationTestCase {
	public function testConnectionUsesADedicatedTestDatabase(): void {
		$statement = $this->getConnection()->query('SELECT current_database()');
		self::assertNotFalse($statement);

		$name = $statement->fetchColumn();
		self::assertIsString($name);
		self::assertStringEndsWith('_test', $name);
	}

	public function testMigrationsHaveBeenApplied(): void {
		$statement = $this->getConnection()->query(
			"SELECT to_regclass('public.phinxlog') IS NOT NULL",
		);
		self::assertNotFalse($statement);
		self::assertTrue(
			(bool) $statement->fetchColumn(),
			'The test database has no phinxlog table. Run `make db-migrate-test`.',
		);
	}

	public function testTruncateAllTablesKeepsTheSchema(): void {
		$this->truncateAllTables();

		$statement = $this->getConnection()->query(
			"SELECT to_regclass('public.phinxlog') IS NOT NULL",
		);
		self::assertNotFalse($statement);
		self::assertTrue((bool) $statement->fetchColumn(), 'truncateAllTables() must not drop the migration log.');
	}
}
