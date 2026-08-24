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
		$request = Request::create('/status', 'GET');

		$response = $kernel->handle($request);

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame('ok', \json_decode((string) $response->getContent(), true)['status']);
	}
}
