<?php

declare(strict_types = 1);

namespace App\App;

use Aura\Sql\ExtendedPdo;
use DI\Container;
use DI\ContainerBuilder;
use Monolog\Logger;
use PDO;
use Psr\Log\LoggerInterface;

use function DI\autowire;
use function DI\create;
use function DI\value;

/** @phpstan-type ConfigArray array<string, string> */
class Services {
	public function __construct(
		private readonly ContainerBuilder $containerBuilder,
		private readonly Config $config
	) {
	}

	public function register(): Container {
		$this->containerBuilder->addDefinitions([
			LoggerInterface::class => create(Logger::class)
				->constructor(value('App')),
			PDO::class => create(ExtendedPdo::class)
				->constructor(
					value(
						\sprintf(
							'pgsql:host=%s;dbname=%s',
							$this->config['DB_HOST'],
							$this->config['DB_NAME']
						)
					),
					value($this->config['DB_USER']),
					value($this->config['DB_PASS'])
				),
		]);
		return $this->containerBuilder->build();
	}
}
