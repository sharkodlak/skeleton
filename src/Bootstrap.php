<?php

declare(strict_types = 1);

namespace App;

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
		$services = new Services($containerBuilder, $config);

		return $services->register();
	}
}
