<?php

declare(strict_types = 1);

namespace Tests\Unit\Fw\Symfony;

use App\App\Fw\Symfony\SymfonyRuntime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SymfonyRuntime::class)]
final class SymfonyRuntimeTest extends TestCase {
	public function testItExposesSymfonyRuntimeStatus(): void {
		$runtime = new SymfonyRuntime();
		$request = Request::create('/status', 'GET');

		$response = $runtime->handle($request);

		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame(
			'8.1',
			\json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR)['symfony'],
		);
	}
}
