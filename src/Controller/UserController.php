<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Dto\CreateUserDto;
use App\Exceptions\UserNotFound;
use App\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

class UserController {
	public function __construct(
		private readonly UserRepository $userRepository
	) {
	}

	public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
		$body = (string) $request->getBody();
		/** @var stdClass $data */
		$data = \json_decode($body, flags: \JSON_THROW_ON_ERROR);
		$userDto = new CreateUserDto($data->username, $data->email);
		$this->userRepository->createUser($userDto);

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
		$data = $this->userRepository->findUserById($userId);

		if ($data === null) {
			throw UserNotFound::create();
		}

		$response->getBody()->write(\json_encode($data, \JSON_THROW_ON_ERROR));
		$response = $response->withHeader('Content-Type', 'application/json');
		return $response;
	}
}
