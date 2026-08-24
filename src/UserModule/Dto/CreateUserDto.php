<?php

declare(strict_types = 1);

namespace App\UserModule\Dto;

use JsonSerializable;

class CreateUserDto implements JsonSerializable {
	public function __construct(
		private readonly string $username,
		private readonly string $email
	) {
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function getUsername(): string {
		return $this->username;
	}

	public function jsonSerialize(): mixed {
		return [
			'username' => $this->username,
			'email' => $this->email,
		];
	}
}
