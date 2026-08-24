<?php

declare(strict_types = 1);

namespace Tests\Integration\Fw\Symfony;

use App\App\Fw\Symfony\SymfonyRuntime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SymfonyRuntime::class)]
final class SymfonyRuntimeIntegrationTest extends TestCase {
	public function testRuntimeReturnsStatusPayloadForRequest(): void {
		$runtime = new SymfonyRuntime();
		$response = $runtime->handle(Request::create('/status', 'GET'));

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame('ok', \json_decode((string) $response->getContent(), true)['status']);
	}
}
