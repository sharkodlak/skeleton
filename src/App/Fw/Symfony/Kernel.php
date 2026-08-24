<?php

declare(strict_types = 1);

namespace App\App\Fw\Symfony;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\HttpKernel\KernelEvents;

final class Kernel extends HttpKernel {
	public function __construct() {
		$dispatcher = new EventDispatcher();
		$dispatcher->addListener(
			KernelEvents::REQUEST,
			static function (RequestEvent $event): void {
				if (RouteConfig::matchesPath($event->getRequest()->getPathInfo())) {
					$event->setResponse(new JsonResponse([ 'status' => 'ok', 'symfony' => SymfonyKernel::VERSION ]));
				}
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
		if (RouteConfig::matchesPath($request->getPathInfo())) {
			return new JsonResponse([ 'status' => 'ok', 'symfony' => SymfonyKernel::VERSION ]);
		}

		return parent::handle($request, $type, $catch);
	}
}
