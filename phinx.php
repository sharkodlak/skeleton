<?php

declare(strict_types = 1);

if (file_exists(__DIR__ . '/.env')) {
	(new Symfony\Component\Dotenv\Dotenv())->usePutenv(true)->load(__DIR__ . '/.env');
}

$dbHost = (string) getenv('DB_HOST');
$dbName = (string) getenv('DB_NAME');
$dbUser = (string) getenv('DB_USER');
$dbPass = (string) getenv('DB_PASS');
$dbPort = (int) getenv('DB_PORT');
// Tests run against a separate database so that a migration or seed can never
// overwrite development data. The name is derived from DB_NAME on purpose.
$dbNameTest = $dbName . '_test';

return [
	'paths' => [
		'migrations' => __DIR__ . '/db/migrations',
		// Namespaced so the seed class name can match its file name, which the
		// coding standard checks. Migrations cannot: Phinx owns their file names.
		'seeds' => ['Db\Seeds' => __DIR__ . '/db/seeds'],
	],
	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_environment' => 'development',
		'production' => [
			'adapter' => 'pgsql',
			'host' => $dbHost,
			'name' => $dbName,
			'user' => $dbUser,
			'pass' => $dbPass,
			'port' => $dbPort,
			'charset' => 'utf8',
		],
		'development' => [
			'adapter' => 'pgsql',
			'host' => $dbHost,
			'name' => $dbName,
			'user' => $dbUser,
			'pass' => $dbPass,
			'port' => $dbPort,
			'charset' => 'utf8',
		],
		'testing' => [
			'adapter' => 'pgsql',
			'host' => $dbHost,
			'name' => $dbNameTest,
			'user' => $dbUser,
			'pass' => $dbPass,
			'port' => $dbPort,
			'charset' => 'utf8',
		],
	],
	'version_order' => 'creation',
];
