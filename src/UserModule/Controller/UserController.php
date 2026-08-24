<?php

declare(strict_types = 1);

namespace App\UserModule\Controller;

use App\UserModule\Dto\CreateUserDto;
use App\UserModule\Service\UserCrudService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class UserController {
	public function __construct(
		private readonly UserCrudService $userCrudService
	) {
	}

	public function checkUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$queryParams = $request->getQueryParams();
		$data = $this->userCrudService->checkUser($queryParams['email'] ?? null, $queryParams['username'] ?? null);

		$response->getBody()->write(\json_encode($data, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response;
	}

	public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$body = (string) $request->getBody();
		/** @var stdClass $data */
		$data = \json_decode($body, flags: \JSON_THROW_ON_ERROR);
		$userDto = new CreateUserDto($data->username, $data->email);
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
		array $parameters
	): ResponseInterface {
		$userId = $parameters['userId'];
		$data = $this->userCrudService->getUser($userId);

		$response->getBody()->write(\json_encode($data, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response;
	}
}
