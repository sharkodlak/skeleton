<?php

declare(strict_types = 1);

namespace App\UserModule\Exceptions;

use Throwable;

/** phpcs:ignoreFile SlevomatCodingStandard.Classes.SuperfluousExceptionNaming.SuperfluousSuffix */
class UserCreateException extends UserRuntimeException implements Throwable {
	public static function create(?string $message = null, ?Throwable $previous = null): self {
		$message ??= 'User creation failed.';
		return new self($message, 409, $previous);
	}
}
