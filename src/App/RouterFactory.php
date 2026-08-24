<?php

declare(strict_types = 1);

namespace App\App;

use App\App\Api\ValidatorFactory;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class RouterFactory {
	public function __construct(
		private readonly ValidatorFactory $validatorFactory,
		private readonly Config $config,
	) {
	}

	/** @param App<\Psr\Container\ContainerInterface|null> $slimApp */
	public function registerRoutes(App $slimApp): void {
		$slimApp->add($this->validatorFactory->createRequestValidator());

		if ($this->config['OPENAPI_VALIDATE_RESPONSES'] === 'true') {
			$slimApp->add($this->validatorFactory->createResponseValidator());
		}

		// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
		/** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
		$slimApp->group('/api/v{apiVersion}', function (RouteCollectorProxy $version): void {
			// Register module-specific route groups here.
		});
		// phpcs:enable
	}
}
