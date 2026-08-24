<?php

declare(strict_types = 1);

namespace App\App\Api;

use League\OpenAPIValidation\PSR7\Exception\MultipleOperationsMismatchForRequest;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\Validation\AddressValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\PathFinder;
use League\OpenAPIValidation\PSR7\ResponseValidator as LeagueResponseValidator;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Throwable;

/**
 * @phpcsSuppress SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 * @phpstan-type Detail = array{
 *   type: string,
 *   keyword?: string,
 *   message: string,
 *   dataPath?: mixed[]|null,
 *   data?: mixed,
 *   where?: string,
 * }
 * @phpstan-type ErrorPayload = array{
 *   operation: array{
 *     method: string,
 *     path: string,
 *   },
 *   status: int,
 *   contentType: string,
 *   summary: string,
 *   details: Detail[],
 * }
 */
class ResponseValidator implements MiddlewareInterface {
	public function __construct(
		private readonly LeagueResponseValidator $validator
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$response = $handler->handle($request);

		if ($this->isStreamingResponse($response)) { // TODO: Validate streaming responses
			return $response;
		}

		$matchingOperations = $this->findMatchingOperations($request);
		$this->ensureOperationExists($request, $matchingOperations);

		return $this->validateResponse($matchingOperations, $response);
	}

	/**
	 * Check if response is streaming (text/event-stream) and should skip validation.
	 */
	private function isStreamingResponse(ResponseInterface $response): bool {
		$contentType = $response->getHeaderLine('Content-Type');
		return \str_contains($contentType, 'text/event-stream');
	}

	/**
	 * Ensure at least one matching operation exists for the request.
	 *
	 * @param array<OperationAddress> $matchingOperations
	 * @throws NoOperation
	 */
	private function ensureOperationExists(ServerRequestInterface $request, array $matchingOperations): void {
		if ($matchingOperations === []) {
			throw NoOperation::fromPathAndMethod($request->getUri()->getPath(), \strtolower($request->getMethod()));
		}
	}

	/**
	 * Validate response against matching operations.
	 *
	 * @param array<OperationAddress> $matchingOperations
	 */
	private function validateResponse(array $matchingOperations, ResponseInterface $response): ResponseInterface {
		if (\count($matchingOperations) === 1) {
			return $this->validateSingleMatch($matchingOperations[0], $response);
		}

		return $this->validateMultipleMatches($matchingOperations, $response);
	}

	/**
	 * Validate response when there are multiple matching operations.
	 *
	 * @param array<OperationAddress> $matchingOperations
	 */
	private function validateMultipleMatches(array $matchingOperations, ResponseInterface $response): ResponseInterface {
		$attempts = [];

		foreach ($matchingOperations as $matchedAddr) {
			try {
				$this->validator->validate($matchedAddr, $response);
				return $response;
			} catch (Throwable $e) {
				$attempts[] = $this->buildErrorPayload($matchedAddr, $response, $e);
			}
		}

		// None of the multiple matching operations validated successfully.
		throw MultipleOperationsMismatchForRequest::fromMatchedAddrs($matchingOperations);
	}

	/**
	 * Check the openapi spec and find matching operations(path+method)
	 * This should consider path parameters as well
	 * "/users/12" should match both ["/users/{id}", "/users/{group}"]
	 *
	 * @return array<OperationAddress>
	 */
	private function findMatchingOperations(ServerRequestInterface $request): array {
		$pathFinder = new PathFinder($this->validator->getSchema(), (string) $request->getUri(), $request->getMethod());
		return $pathFinder->search();
	}

	/**
	 * There is only one matching operation, so we can validate directly against it.
	 */
	private function validateSingleMatch(OperationAddress $address, ResponseInterface $response): ResponseInterface {
		try {
			$this->validator->validate($address, $response);
			return $response;
		} catch (Throwable $e) {
			$errors = $this->buildErrorPayload($address, $response, $e);
			return $this->failJson(500, 'Response validation failed', $errors);
		}
	}

	/**
	 * Extracts specific places and reasons (keyword, dataPath, schemaPath, message) from the exceptions.
	 *
	 * @return ErrorPayload
	 */
	private function buildErrorPayload(OperationAddress $addr, ResponseInterface $response, Throwable $e): array {
		$details = [];

		for ($t = $e; $t; $t = $t->getPrevious()) {
			if ($t instanceof KeywordMismatch) {
				$details[] = $this->getErrorDetailsForKeywordMismatch($t);
			} elseif ($t instanceof SchemaMismatch) {
				$details[] = $this->getErrorDetailsForSchemaMismatch($t);
			} elseif ($t instanceof AddressValidationFailed) {
				$details[] = $this->getErrorDetailsForAddressValidationFailed($t);
			}
		}

		return [
			'operation' => [
				'method' => \strtolower($addr->method()),
				'path' => $addr->path(),
			],
			'status' => $response->getStatusCode(),
			'contentType' => $response->getHeaderLine('Content-Type'),
			'summary' => $e->getMessage(),
			'details' => $details,
		];
	}

	/**
	 * @return Detail
	 */
	private function getErrorDetailsForKeywordMismatch(KeywordMismatch $e): array {
		return [
			'type' => 'KeywordMismatch',
			'keyword' => $e->keyword(),
			'message' => $e->getMessage(),
			'dataPath' => $e->dataBreadCrumb()?->buildChain(),
			'data' => $e->data(),
		];
	}

	/**
	 * @return Detail
	 */
	private function getErrorDetailsForSchemaMismatch(SchemaMismatch $e): array {
		return [
			'type' => (new ReflectionClass($e))->getShortName(),
			'message' => $e->getMessage(),
			'dataPath' => $e->dataBreadCrumb()?->buildChain(),
			'data' => $e->data(),
		];
	}

	/**
	 * @return Detail
	 */
	private function getErrorDetailsForAddressValidationFailed(AddressValidationFailed $e): array {
		return [
			'type' => 'AddressValidationFailed',
			'message' => $e->getMessage(),
			'where' => (string) $e->getAddress(),
		];
	}

	/**
	 * @param ErrorPayload $errors
	 */
	private function failJson(int $status, string $title, array $errors): ResponseInterface {
		$data = [
			'error' => $title,
			'success' => false,
			'errors' => $errors,
		];
		$json = \json_encode($data, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);

		$response = new Response($status);
		$response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
		$response->getBody()->write($json);

		return $response;
	}
}
