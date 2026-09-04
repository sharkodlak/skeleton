<?php

declare(strict_types = 1);

namespace App\App;

use App\App\View\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

final readonly class Application {
	private const string STATUS_PATH = '/api/status';

	public function __construct(
		private TemplateRenderer $renderer = new TemplateRenderer(),
	) {
	}

	public function handle(Request $request): Response {
		$framework = \explode('\\', SymfonyKernel::class)[0];

		if ($request->getPathInfo() === '/') {
			return new Response($this->renderer->render('home.php', [
				'message' => 'Hello ' . $framework . ' ' . SymfonyKernel::VERSION,
				'framework' => $framework,
				'version' => SymfonyKernel::VERSION,
			]));
		}

		if ($request->getPathInfo() === self::STATUS_PATH) {
			return new JsonResponse([ 'status' => 'ok', 'symfony' => SymfonyKernel::VERSION ]);
		}

		return new Response('Not found', Response::HTTP_NOT_FOUND);
	}
}
