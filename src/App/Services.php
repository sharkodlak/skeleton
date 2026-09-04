<?php

declare(strict_types = 1);

namespace App\App;

use App\App\Api\ValidatorFactory;
use App\Exceptions\AppRuntimeException;
use Aura\Sql\ExtendedPdo;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Definition;
use DI\Definition\Helper\DefinitionHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
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
			// Hand out the instance Bootstrap built, which knows the absolute path
			// to .env. Without this the container autowires a fresh Config with the
			// relative default and fails wherever the working directory is not the
			// project root -- php-fpm, for one.
			Config::class => value($this->config),
			LoggerInterface::class => create(Logger::class)
				->constructor(value('App'), value([ $this->logHandler() ])),
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

	/**
	 * Logs go to stderr, so the container runtime collects them; nothing writes
	 * files out of the box. A project that wants files can swap this handler --
	 * logrotate is already configured for var/log in the image.
	 */
	private function logHandler(): StreamHandler {
		return new StreamHandler('php://stderr', $this->logLevel());
	}

	/**
	 * Monolog's own converters are annotated with a union of string literals, which
	 * a value read from configuration can never satisfy, so the mapping is spelled
	 * out here instead.
	 */
	private function logLevel(): Level {
		if (!isset($this->config['LOGGER_LEVEL'])) {
			return Level::Debug;
		}

		$name = \strtolower($this->config['LOGGER_LEVEL']);

		return match ($name) {
			'debug' => Level::Debug,
			'info' => Level::Info,
			'notice' => Level::Notice,
			'warning' => Level::Warning,
			'error' => Level::Error,
			'critical' => Level::Critical,
			'alert' => Level::Alert,
			'emergency' => Level::Emergency,
			default => throw new AppRuntimeException(\sprintf('Unknown LOGGER_LEVEL "%s".', $name)),
		};
	}
}
