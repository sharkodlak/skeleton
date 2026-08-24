<?php

declare(strict_types = 1);

namespace App\App;

use ArrayAccess;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

/** @implements ArrayAccess<string, string> */
class Config implements ArrayAccess {
	public function __construct(
		Dotenv $dotenv,
		string $envFile = '.env',
	) {
		$dotenv->load($envFile);
	}

	public function offsetExists(mixed $offset): bool {
		$offset = (string) $offset;
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		return isset($_ENV[$offset]);
	}

	public function offsetGet(mixed $offset): string {
		$offset = (string) $offset;
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		$value = $_ENV[$offset];
		\assert(\is_string($value));
		return $value;
	}

	/** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
	public function offsetSet(mixed $offset, mixed $value): void {
		throw new RuntimeException('Config is read-only');
	}

	/** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
	public function offsetUnset(mixed $offset): void {
		throw new RuntimeException('Config is read-only');
	}
}
