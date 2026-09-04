<?php

declare(strict_types = 1);

namespace Tests\Smoke;

/**
 * The smallest set of read-only checks worth running against a deployment
 * before calling it healthy.
 */
final class DeploymentSmokeTest extends SmokeTestCase {
	public function testSiteAnswers(): void {
		$response = $this->http()->get('/');

		self::assertGreaterThanOrEqual(200, $response->status);
		self::assertLessThan(400, $response->status, 'The deployed site did not answer with a success status.');
	}

	public function testApplicationIsExecutedAndNotServedAsSource(): void {
		$response = $this->http()->get('/index.php');

		self::assertLessThan(500, $response->status, 'The application returned a server error.');
		self::assertStringNotContainsString('<?php', $response->body, 'PHP source is being served verbatim.');
	}
}
