<?php

declare(strict_types = 1);

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Base class for tests that talk to a real database.
 *
 * The connection always points at the test database, which is a sibling of the
 * application database created by the postgres image (see
 * containers/images/postgre/create-test-db.sh) and migrated by
 * `composer cmd:db:migrate:test`. tests/bootstrap.php rewrites DB_NAME before
 * anything else runs; the guard below turns a broken setup into a failed test
 * rather than a silently mutated development database.
 */
abstract class IntegrationTestCase extends TestCase {
	private static ?PDO $connection = null;

	private static function connect(): PDO {
		$name = self::readEnv('DB_NAME');

		if (!\str_ends_with($name, '_test')) {
			throw new RuntimeException(
				\sprintf(
					'Refusing to connect to database "%s": integration tests only run against a database whose'
					. ' name ends with "_test". tests/bootstrap.php is responsible for rewriting DB_NAME.',
					$name,
				),
			);
		}

		$dsn = \sprintf(
			'pgsql:host=%s;port=%s;dbname=%s',
			self::readEnv('DB_HOST'),
			self::readEnv('DB_PORT'),
			$name,
		);

		return new PDO($dsn, self::readEnv('DB_USER'), self::readEnv('DB_PASS'), [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
	}

	private static function readEnv(string $name): string {
		$value = \getenv($name);

		if ($value === false || $value === '') {
			throw new RuntimeException(\sprintf('Environment variable %s is not set.', $name));
		}

		return $value;
	}

	protected function getConnection(): PDO {
		if (self::$connection === null) {
			self::$connection = self::connect();
		}

		return self::$connection;
	}

	/**
	 * Empties every table of the test schema, leaving the schema itself and the
	 * Phinx migration log intact. Call it from setUp() to give each test a known
	 * starting point.
	 */
	protected function truncateAllTables(): void {
		$connection = $this->getConnection();
		$statement = $connection->query(
			"SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename <> 'phinxlog'",
		);

		if ($statement === false) {
			throw new RuntimeException('Unable to list the tables of the test database.');
		}

		/** @var list<string> $tables */
		$tables = $statement->fetchAll(PDO::FETCH_COLUMN);

		if ($tables === []) {
			return;
		}

		$quoted = \array_map(static fn (string $table): string => '"' . $table . '"', $tables);
		$connection->exec('TRUNCATE TABLE ' . \implode(', ', $quoted) . ' RESTART IDENTITY CASCADE');
	}
}
