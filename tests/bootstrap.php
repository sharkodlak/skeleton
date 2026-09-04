<?php

declare(strict_types = 1);

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

/*
 * Safety net: the test suite must never reach the development database.
 *
 * The test database name is derived from DB_NAME by appending `_test`, so there
 * is a single variable to configure.
 *
 * The .env file is parsed and applied by hand rather than through
 * Dotenv::load(). Loading it would register every name in SYMFONY_DOTENV_VARS,
 * and the Dotenv::load() call during the application bootstrap would then treat
 * those variables as its own and overwrite the DB_NAME forced below. Setting
 * them directly makes them look like pre-existing environment variables, which
 * Dotenv leaves alone.
 */
$envFile = __DIR__ . '/../.env';

if (is_file($envFile)) {
	$contents = file_get_contents($envFile);
	assert(is_string($contents));

	// Dotenv::parse() is annotated as a plain array, so the entries are narrowed
	// at runtime below rather than through a type hint the standard disallows.
	$values = (new Dotenv())->parse($contents, $envFile);

	foreach ($values as $name => $value) {
		if (!is_string($name) || !is_string($value) || getenv($name) !== false) {
			continue;
		}

		putenv($name . '=' . $value);
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		$_ENV[$name] = $value;
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		$_SERVER[$name] = $value;
	}
}

$dbName = (string) getenv('DB_NAME');

if ($dbName !== '' && !str_ends_with($dbName, '_test')) {
	$dbName .= '_test';
	putenv('DB_NAME=' . $dbName);
	// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
	$_ENV['DB_NAME'] = $dbName;
	// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
	$_SERVER['DB_NAME'] = $dbName;
}
