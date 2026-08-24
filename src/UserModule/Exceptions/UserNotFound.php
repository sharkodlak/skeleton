<?php

declare(strict_types = 1);

namespace App\UserModule\Exceptions;

use Throwable;

class UserNotFound extends UserCreateException {
	public static function create(?string $message = null, ?Throwable $previous = null): self {
		$message ??= 'User not found.';
		return new self($message, 404, $previous);
	}
}
