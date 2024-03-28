<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Dto\CreateUserDto;
use App\Entity\User;

interface UserRepository {
	public function createUser(CreateUserDto $newUser): void;

	public function findUserById(string $id): ?User;

	public function findUserByUsername(string $username): ?User;
}
