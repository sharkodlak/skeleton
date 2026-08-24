<?php

declare(strict_types = 1);

namespace App\App;

use App\App\Api\ErrorHandler;
use ErrorException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

/**
 * @phpstan-type T = ContainerInterface|null
 */
class SlimAppFactory {
	public function __construct(
		private readonly ContainerInterface $container,
		private readonly RouterFactory $routerFactory,
		private readonly Config $config,
	) {
	}

	/**
	 * @return App<T>
	 */
	public function create(): App {
		AppFactory::setContainer($this->container);
		/** @var App<T> $app */
		$app = AppFactory::create();

		$this->routerFactory->registerRoutes($app);
		$this->registerErrorMiddleware($app);

		return $app;
	}

	/**
	 * @param App<T> $app
	 */
	private function registerErrorMiddleware(App $app): void {
		\ini_set('display_errors', 'Off');
		\set_error_handler(
			fn (int $severity, string $message, string $file, int $line) => $this->errorHandler(
				$severity,
				$message,
				$file,
				$line
			),
			\E_ALL
		);
		$errorMiddleware = $app->addErrorMiddleware($this->config['DISPLAY_ERROR_DETAILS'] === 'true', true, true);
		$errorHandler = $this->container->get(ErrorHandler::class);
		\assert($errorHandler instanceof ErrorHandler);
		$errorMiddleware->setDefaultErrorHandler($errorHandler);
	}

	private function errorHandler(int $severity, string $message, string $file, int $line): false {
		$matchSeverityMask = \error_reporting() & $severity;

		if ($matchSeverityMask === 0) {
			return false;
		}

		throw new ErrorException($message, 0, $severity, $file, $line);
	}
}
