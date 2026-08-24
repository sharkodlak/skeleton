<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Throwable;

/** phpcsSuppress SlevomatCodingStandard.Classes.SuperfluousExceptionNaming.SuperfluousSuffix */
class InvalidParameterException extends AppRuntimeException {
	public function __construct(string $message, int $code = 400, ?Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
