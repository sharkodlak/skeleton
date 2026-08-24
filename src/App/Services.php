<?php

declare(strict_types = 1);

namespace App\App;

use App\App\Api\ValidatorFactory;
use Aura\Sql\ExtendedPdo;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Definition;
use DI\Definition\Helper\DefinitionHelper;
use Monolog\Logger;
use PDO;
use Psr\Log\LoggerInterface;

use function DI\create;
use function DI\value;

class Services {
	/** @param ContainerBuilder<Container> $containerBuilder */
	public function __construct(
		private readonly ContainerBuilder $containerBuilder,
		private readonly Config $config,
		private readonly ValidatorFactory $validatorFactory,
	) {
	}

	public function register(): Container {
		$this->containerBuilder->addDefinitions([
			...$this->coreDefinition(),
			...$this->validatorFactoryDefinition(),
		]);
		return $this->containerBuilder->build();
	}

	/** @return array<class-string, DefinitionHelper|Definition> */
	private function coreDefinition(): array {
		return [
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
		];
	}

	/**
	 * Framework-agnostic; FW branches fetch this instead of constructing their own.
	 *
	 * @return array<class-string, DefinitionHelper|Definition>
	 */
	private function validatorFactoryDefinition(): array {
		return [
			ValidatorFactory::class => value($this->validatorFactory),
		];
	}
}
