<?php

declare(strict_types = 1);

namespace Tests\Unit\App;

use App\App\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

final class ApplicationTest extends TestCase {
	public function testItRendersHomePage(): void {
		$app = new Application();
		$response = $app->handle(Request::create('/', 'GET'));

		self::assertSame(200, $response->getStatusCode());
		self::assertStringContainsString('Hello Symfony', (string) $response->getContent());
		self::assertStringContainsString(Kernel::VERSION, (string) $response->getContent());
	}

	public function testItReturnsStatusJsonForApiRoute(): void {
		$app = new Application();
		$response = $app->handle(Request::create('/api/status', 'GET'));

		self::assertSame(200, $response->getStatusCode());

		$payload = \json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
		self::assertIsArray($payload);
		self::assertSame('ok', $payload['status']);
	}
}
