<?php

declare(strict_types = 1);

namespace App\UserModule\Infrastructure;

use App\UserModule\Dto\CreateUserDto;
use App\UserModule\Entity\User;
use App\UserModule\Exceptions\UserAlreadyExists;
use App\UserModule\Exceptions\UserCreateException;
use App\UserModule\Repository\UserRepository;
use App\UserModule\ValueObject\Email;
use App\UserModule\ValueObject\UserId;
use App\UserModule\ValueObject\UserName;
use PDO;
use PDOStatement;

/**
 * @phpstan-type UserRow array{
 *   id: string,
 * }
 */
class UserRepositoryImpl implements UserRepository {
	public function __construct(
		private readonly PDO $pdo,
	) {
	}

	public function createUser(CreateUserDto $newUser): void {
		$this->validateNewUser($newUser);
		$user = $this->findUserByUsername($newUser->getUsername());

		if ($user !== null) {
			throw UserAlreadyExists::create('User already exists');
		}

		$stmt = $this->pdo->prepare(
			'INSERT INTO users (username, email, updated_at) VALUES (:username, :email, NOW())'
		);
		$stmt->execute([
			'email' => $newUser->getEmail(),
			'username' => $newUser->getUsername(),
		]);
	}

	public function findUserByEmail(string $email): ?User {
		$stmt = $this->pdo->prepare('SELECT user_id, username, email FROM users WHERE email = :email');
		$stmt->execute([ 'email' => $email ]);
		return $this->fetch($stmt);
	}

	public function findUserById(string $id): ?User {
		$stmt = $this->pdo->prepare('SELECT user_id, username, email FROM users WHERE user_id = :id');
		$stmt->execute([ 'id' => $id ]);
		return $this->fetch($stmt);
	}

	public function findUserByUsername(string $username): ?User {
		$stmt = $this->pdo->prepare('SELECT user_id, username, email FROM users WHERE username = :username');
		$stmt->execute([ 'username' => $username ]);
		return $this->fetch($stmt);
	}

	private function fetch(PDOStatement $stmt): ?User {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		\assert(\is_array($row) || $row === false, 'Unexpected fetch result');

		if ($row === false) {
			return null;
		}

		$userId = $row['user_id'];
		$username = $row['username'];
		$email = $row['email'];
		\assert(\is_string($userId), 'Unexpected user_id type');
		\assert(\is_string($username), 'Unexpected username type');
		\assert(\is_string($email), 'Unexpected email type');

		return new User(new UserId($userId), new UserName($username), new Email($email));
	}

	private function validateNewUser(CreateUserDto $newUser): void {
		if ($newUser->getUsername() === '') {
			throw new UserCreateException('Username cannot be empty');
		}

		if ($newUser->getEmail() === '') {
			throw new UserCreateException('Email cannot be empty');
		}
	}
}
