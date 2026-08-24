<?php

declare(strict_types = 1);

namespace App\App\Fw\Symfony;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class SymfonyRuntime {
	public function handle(Request $request): Response {
		$routes = new RouteCollection();
		$routes->add('status', new Route('/status', [
			'_controller' => static fn (): Response => new JsonResponse([
				'status' => 'ok',
				'symfony' => '8.1',
			]),
		]));

		$context = new RequestContext();
		$context->fromRequest($request);
		$matcher = new UrlMatcher($routes, $context);
		$match = $matcher->matchRequest($request);

		$payload = [
			'status' => 'ok',
			'symfony' => '8.1',
			'path' => $request->getPathInfo(),
			'route' => $match['_route'] ?? 'default',
		];

		return new JsonResponse($payload, Response::HTTP_OK);
	}

	public function buildConsole(): Application {
		$app = new Application('skeleton', '8.1');
		$app->addCommand(new class extends Command {
			protected static string $defaultName = 'app:status';

			public function __construct() {
				parent::__construct(self::$defaultName);
			}

			protected function configure(): void {
				$this->setDescription('Reports Symfony runtime status.');
			}

			protected function execute(InputInterface $unusedInput, OutputInterface $output): int {
				unset($unusedInput);
				$output->writeln('Symfony 8.1 runtime is active.');
				return self::SUCCESS;
			}
		});

		return $app;
	}

	public function buildContainer(): ContainerBuilder {
		$container = new ContainerBuilder();
		$container->set('event_dispatcher', new EventDispatcher());

		return $container;
	}

	public function createKernel(): HttpKernelInterface {
		$dispatcher = new EventDispatcher();
		$dispatcher->addListener(KernelEvents::REQUEST, static function (): void {
			// no-op listener to exercise the event dispatcher.
		});

		return new HttpKernel(
			$dispatcher,
			new ControllerResolver(),
			null,
			new ArgumentResolver(),
		);
	}

	/** @return array<string, array{path: string}> */
	public function routes(): array {
		return RouteConfig::all();
	}
}
