<?php

declare(strict_types = 1);

namespace App\UserModule\Repository;

use App\UserModule\Dto\CreateUserDto;
use App\UserModule\Entity\User;

interface UserRepository {
	public function createUser(CreateUserDto $newUser): void;

	public function findUserById(string $id): ?User;

	public function findUserByEmail(string $email): ?User;

	public function findUserByUsername(string $username): ?User;
}
