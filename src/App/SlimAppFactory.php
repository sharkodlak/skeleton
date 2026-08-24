<?php

declare(strict_types = 1);

namespace App\App;

use App\App\Api\ErrorHandler;
use App\App\Api\ValidatorFactory;
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

class SlimAppFactory {
	public function __construct(
		private readonly Container $container,
		private readonly RouterFactory $routerFactory,
		private readonly ValidatorFactory $validatorFactory,
		private readonly Config $config
	) {
	}

	public function create(): App {
		AppFactory::setContainer($this->container);
		$app = AppFactory::create();

		$requestValidator = $this->validatorFactory->createRequestValidator();
		$app->add($requestValidator);

		if ($this->config['OPENAPI_VALIDATE_RESPONSES'] === 'true') {
			$responseValidator = $this->validatorFactory->createResponseValidator();
			$app->add($responseValidator);
		}

		$errorMiddleware = $app->addErrorMiddleware($this->config['DISPLAY_ERROR_DETAILS'] === 'true', true, true);
		$errorHandler = $this->container->get(ErrorHandler::class);
		\assert($errorHandler instanceof ErrorHandler);
		$errorMiddleware->setDefaultErrorHandler($errorHandler);
		$this->routerFactory->registerRoutes($app);

		return $app;
	}
}
