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
		$response = $runtime->handle(Request::create('/api/status', 'GET'));

		self::assertSame(200, $response->getStatusCode());

		$payload = \json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
		self::assertIsArray($payload);
		self::assertSame('ok', $payload['status']);
	}
}
