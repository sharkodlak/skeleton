<?php

declare(strict_types = 1);

namespace App\App\Api;

use App\Exceptions\UserRuntimeException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Throwable;

/**
 * @phpstan-type DataArray = array{
 *   code: int,
 *   error: string,
 *   trace?: array,
 *   exception?: Throwable
 * }
 */
class ErrorHandler {
	public function __construct(
		private readonly LoggerInterface $logger
	) {
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function __invoke(
		ServerRequestInterface $request,
		Throwable $exception,
		bool $displayErrorDetails,
		bool $logErrors,
		bool $logErrorDetails
	): ResponseInterface {
		$statusCode = $exception instanceof HttpException
			|| $exception instanceof UserRuntimeException
			? $exception->getCode()
			: 500;
		$errorMessage = $exception->getMessage() !== '' ? $exception->getMessage() : 'Internal Server Error';
		$data = [
			'code' => $statusCode,
			'error' => $errorMessage,
		];

		if ($displayErrorDetails) {
			$data['trace'] = $exception->getTrace();
		}

		$this->logError($logErrors, $logErrorDetails, $data, $exception);

		$response = new Response($statusCode);
		$response = $response->withHeader('Content-Type', 'application/json');
		$json = \json_encode($data, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
		$response->getBody()->write($json);

		return $response;
	}

	/**
	 * @param DataArray $data
	 */
	private function logError(bool $logErrors, bool $logErrorDetails, array $data, Throwable $exception): void {
		if (!$logErrors) {
			return;
		}

		if ($logErrorDetails) {
			$data['exception'] = $exception;
		}

		$this->logger->error($data['error'], $data);
	}
}
