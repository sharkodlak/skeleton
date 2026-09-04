<?php

declare(strict_types = 1);

namespace App\App\Log;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Stamps log records with the request id nginx generated, so a line in the
 * application log can be traced back to the entry in the access log.
 *
 * The id arrives as the HTTP_X_REQUEST_ID FastCGI parameter. Requests that do
 * not carry one, and anything running on the command line, are left untouched.
 */
final class RequestIdProcessor implements ProcessorInterface {
	public function __invoke(LogRecord $record): LogRecord {
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;

		if (!\is_string($requestId) || $requestId === '') {
			return $record;
		}

		$record->extra['request_id'] = $requestId;

		return $record;
	}
}
