<?php

declare(strict_types = 1);

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\HttpClient;

/**
 * Base class for read-only health checks against an already-deployed environment.
 *
 * This suite is deliberately kept out of `composer cmd:qa`. QA runs in fresh and
 * CI-like environments where nothing is deployed yet; a smoke check there would
 * either fail or, worse, be pointed at a stack it should not touch. Run it
 * explicitly after a deploy:
 *
 *     SMOKE_BASE_URL=https://example.com composer cmd:tests:smoke
 *
 * Tests here must never write: the target may well be production.
 */
abstract class SmokeTestCase extends TestCase {
	protected function http(): HttpClient {
		return new HttpClient($this->baseUrl());
	}

	private function baseUrl(): string {
		$baseUrl = \getenv('SMOKE_BASE_URL');

		if ($baseUrl === false || $baseUrl === '') {
			self::markTestSkipped('Set SMOKE_BASE_URL to the deployed environment to run the smoke suite.');
		}

		return $baseUrl;
	}
}
