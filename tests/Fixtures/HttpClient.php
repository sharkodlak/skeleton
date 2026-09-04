<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

use RuntimeException;

/**
 * Minimal HTTP client for suites that exercise the running stack.
 *
 * Deliberately built on the stream wrappers rather than a dependency: these
 * suites are meant to work against a deployed environment, where the point is
 * to test the stack, not the client.
 */
final readonly class HttpClient {
	/** @param list<string> $headers */
	private static function statusOf(array $headers): int {
		$status = 0;

		foreach ($headers as $header) {
			// A redirect chain yields several status lines; the last one is the answer.
			if (\preg_match('~^HTTP/\S+\s+(\d{3})~', $header, $matches) === 1) {
				$status = (int) $matches[1];
			}
		}

		if ($status === 0) {
			throw new RuntimeException('The response carried no HTTP status line.');
		}

		return $status;
	}

	public function __construct(
		private string $baseUrl,
		private int $timeoutSeconds = 10,
	) {
	}

	public function get(string $path): HttpResponse {
		$url = \rtrim($this->baseUrl, '/') . '/' . \ltrim($path, '/');
		$context = \stream_context_create([
			'http' => [
				'method' => 'GET',
				// Return the body of 4xx and 5xx responses instead of failing.
				'ignore_errors' => true,
				'timeout' => $this->timeoutSeconds,
				'follow_location' => 0,
			],
		]);

		$body = \file_get_contents($url, false, $context);

		if ($body === false) {
			throw new RuntimeException(\sprintf('GET %s could not be performed.', $url));
		}

		// PHP 8.5 replaced the locally scoped $http_response_header with this call.
		$headers = \http_get_last_response_headers() ?? [];

		return new HttpResponse(self::statusOf($headers), $body, $headers);
	}
}
