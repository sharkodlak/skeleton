<?php

declare(strict_types = 1);

namespace App\App\Fw\Symfony;

use App\App\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;

final class Kernel extends HttpKernel {
	public function __construct() {
		$app = new Application();
		$dispatcher = new EventDispatcher();
		$dispatcher->addListener(
			KernelEvents::REQUEST,
			static function (RequestEvent $event) use ($app): void {
				$event->setResponse($app->handle($event->getRequest()));
			},
		);

		parent::__construct(
			$dispatcher,
			new ControllerResolver(),
			null,
			new ArgumentResolver(),
		);
	}

	public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
		unset($type, $catch);
		$app = new Application();
		return $app->handle($request);
	}
}
