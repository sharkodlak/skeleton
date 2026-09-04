<?php

declare(strict_types = 1);

namespace Tests\Integration\Fw\Symfony;

use App\App\Fw\Symfony\Kernel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Kernel::class)]
final class KernelIntegrationTest extends TestCase {
	public function testKernelHandlesStatusRequest(): void {
		$kernel = new Kernel();
		$request = Request::create('/api/status', 'GET');

		$response = $kernel->handle($request);

		self::assertSame(200, $response->getStatusCode());

		$payload = \json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
		self::assertIsArray($payload);
		self::assertSame('ok', $payload['status']);
	}
}
