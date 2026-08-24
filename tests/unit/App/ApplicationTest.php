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

		$this->assertSame(200, $response->getStatusCode());
		$this->assertStringContainsString('Hello Symfony', (string) $response->getContent());
		$this->assertStringContainsString(Kernel::VERSION, (string) $response->getContent());
	}

	public function testItReturnsStatusJsonForApiRoute(): void {
		$app = new Application();
		$response = $app->handle(Request::create('/api/status', 'GET'));

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame('ok', \json_decode((string) $response->getContent(), true)['status']);
	}
}
