<?php

declare(strict_types = 1);

namespace Tests\E2e;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\HttpClient;

/**
 * Base class for tests that drive the stack the way a browser does: over HTTP,
 * through nginx, into php-fpm.
 *
 * These run against the local stack that `make up` starts, so they may create
 * whatever state they need. That is what separates them from the smoke suite,
 * which probes a deployed environment read-only.
 */
abstract class E2eTestCase extends TestCase {
	protected function http(): HttpClient {
		$baseUrl = \getenv('E2E_BASE_URL');

		return new HttpClient($baseUrl === false || $baseUrl === '' ? 'http://web' : $baseUrl);
	}
}
