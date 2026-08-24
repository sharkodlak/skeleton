<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Throwable;

/** phpcs:ignoreFile SlevomatCodingStandard.Classes.SuperfluousExceptionNaming.SuperfluousSuffix */
class InvalidSecurityException extends AppRuntimeException {
	public function __construct(string $message = 'Authentication required', int $code = 401, ?Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
