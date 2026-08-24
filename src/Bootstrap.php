<?php

declare(strict_types = 1);

namespace App;

use App\App\Api\ValidatorFactory;
use App\App\Config;
use App\App\Services;
use DI\Container;
use DI\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

class Bootstrap {
	public static function boot(): Container {
		$containerBuilder = new ContainerBuilder();
		$dotenv = new Dotenv();
		$config = new Config($dotenv, __DIR__ . '/../.env');
		$openApiFile = __DIR__ . '/../openapi.yaml';
		$cacheDir = __DIR__ . '/../var/cache/openapi';
		$validatorFactory = new ValidatorFactory($openApiFile, $cacheDir);
		$services = new Services($containerBuilder, $config, $validatorFactory);

		return $services->register();
	}
}
