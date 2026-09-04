<?php

declare(strict_types = 1);

namespace Tests\E2e;

/**
 * Covers the wiring that broke repeatedly in projects built on this skeleton:
 * nginx serving the static files it carries, and php-fpm actually executing the
 * application behind the ${PHP_UPSTREAM} the config template expands.
 */
final class HttpStackTest extends E2eTestCase {
	public function testStaticEntrypointIsServed(): void {
		$response = $this->http()->get('/');

		self::assertSame(200, $response->status);
		self::assertStringContainsString('text/html', (string) $response->header('Content-Type'));
		self::assertNotSame('', $response->body);
	}

	public function testStaticAssetIsServed(): void {
		$response = $this->http()->get('/manifest.json');

		self::assertSame(200, $response->status);
		self::assertIsArray(\json_decode($response->body, true, 512, \JSON_THROW_ON_ERROR));
	}

	public function testApplicationIsExecutedByPhpFpm(): void {
		$response = $this->http()->get('/index.php');

		self::assertSame(200, $response->status);
		self::assertStringNotContainsString('<?php', $response->body, 'nginx served the source instead of running it.');
		self::assertStringContainsString('Skeleton booted successfully', $response->body);
	}
}
