<?php

declare(strict_types = 1);

namespace App\UserModule\Service;

use App\UserModule\Dto\CreateUserDto;
use App\UserModule\Dto\UserCheckDto;
use App\UserModule\Entity\User;
use App\UserModule\Exceptions\UserAlreadyExists;
use App\UserModule\Exceptions\UserNotFound;
use App\UserModule\Repository\UserRepository;
use InvalidArgumentException;

class UserCrudService {
	public function __construct(
		private readonly UserRepository $userRepository
	) {
	}

	public function checkUser(?string $email, ?string $username): UserCheckDto {
		if ($email === null && $username === null) {
			throw new InvalidArgumentException('Either email or username must be provided.');
		}

		$userCheckDto = new UserCheckDto();

		if ($email !== null) {
			$available = $this->checkEmailAvailability($email);
			$userCheckDto->setEmailAvailable($available);
		}

		if ($username !== null) {
			$available = $this->checkUsernameAvailability($username);
			$userCheckDto->setUsernameAvailable($available);
		}

		if ($userCheckDto->isUsed()) {
			$e = UserAlreadyExists::create('User with this email or username already exists.');
			$e->setExtra($userCheckDto);
			throw $e;
		}

		return $userCheckDto;
	}

	public function checkEmailAvailability(string $email): bool {
		$user = $this->userRepository->findUserByEmail($email);
		return $user === null;
	}

	public function checkUsernameAvailability(string $username): bool {
		$user = $this->userRepository->findUserByUsername($username);
		return $user === null;
	}

	public function createUser(CreateUserDto $newUser): void {
		$this->userRepository->createUser($newUser);
	}

	public function getUser(string $userId): User {
		$user = $this->userRepository->findUserById($userId);

		if ($user === null) {
			throw UserNotFound::create();
		}

		return $user;
	}
}
