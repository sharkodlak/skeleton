<?php

declare(strict_types = 1);

namespace App\UserModule\Controller;

use App\Exceptions\InvalidParameterException;
use App\UserModule\Dto\CreateUserDto;
use App\UserModule\Service\UserCrudService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class UserController {
	/**
	 * Request data reaches the controller untyped. On a framework branch the
	 * OpenAPI request validator has usually rejected bad input long before this
	 * point; these guards make the failure a 400 rather than a TypeError on the
	 * paths where it has not.
	 */
	private static function optionalString(mixed $value, string $name): ?string {
		if ($value === null || \is_string($value)) {
			return $value;
		}

		throw new InvalidParameterException(\sprintf('Parameter "%s" must be a string.', $name));
	}

	private static function requiredString(mixed $value, string $name): string {
		if (\is_string($value)) {
			return $value;
		}

		throw new InvalidParameterException(\sprintf('Property "%s" must be a string.', $name));
	}

	public function __construct(
		private readonly UserCrudService $userCrudService,
	) {
	}

	public function checkUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$queryParams = $request->getQueryParams();
		$data = $this->userCrudService->checkUser(
			self::optionalString($queryParams['email'] ?? null, 'email'),
			self::optionalString($queryParams['username'] ?? null, 'username'),
		);

		$response->getBody()->write(\json_encode($data, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response;
	}

	public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$body = (string) $request->getBody();
		/** @var stdClass $data */
		$data = \json_decode($body, flags: \JSON_THROW_ON_ERROR);
		$userDto = new CreateUserDto(
			self::requiredString($data->username ?? null, 'username'),
			self::requiredString($data->email ?? null, 'email'),
		);
		$this->userCrudService->createUser($userDto);

		$response->getBody()->write(\json_encode($userDto, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response->withStatus(201);
	}

	/**
	 * @param array{userId: string} $parameters
	 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function getUser(
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $parameters,
	): ResponseInterface {
		$userId = $parameters['userId'];
		$data = $this->userCrudService->getUser($userId);

		$response->getBody()->write(\json_encode($data, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response;
	}
}
