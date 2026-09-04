<?php

declare(strict_types = 1);

namespace Tests\Fixtures;

/** One HTTP response, reduced to what the end-to-end and smoke suites assert on. */
final readonly class HttpResponse {
	/** @param list<string> $headers */
	public function __construct(
		public int $status,
		public string $body,
		public array $headers,
	) {
	}

	public function header(string $name): ?string {
		$prefix = \strtolower($name) . ':';

		foreach ($this->headers as $header) {
			if (\str_starts_with(\strtolower($header), $prefix)) {
				return \trim(\substr($header, \strlen($prefix)));
			}
		}

		return null;
	}
}
