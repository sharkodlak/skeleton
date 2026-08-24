<?php

declare(strict_types = 1);

namespace App\App;

use App\UserModule\Controller\UserController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class RouterFactory {
	public function registerRoutes(App $slimApp): void {
		// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure
		$slimApp->group('/api/v{apiVersion}', function (RouteCollectorProxy $version): void {
			$version->group('/user', function (RouteCollectorProxy $user): void {
				$user->post('', UserController::class . ':createUser');
				$user->get('/check', UserController::class . ':checkUser');
				$user->get('/{userId}', UserController::class . ':getUser');
			});
		});
		// phpcs:enable
	}
}
