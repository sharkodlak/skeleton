<?php

declare(strict_types = 1);

namespace App\UserModule\Exceptions;

use Throwable;

class UserAlreadyExists extends UserCreateException {
	public static function create(?string $message = null, ?Throwable $previous = null): self {
		$message ??= 'User already exists.';
		return new self($message, 409, $previous);
	}
}
