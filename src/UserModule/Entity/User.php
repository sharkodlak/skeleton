<?php

declare(strict_types = 1);

namespace App\UserModule\Entity;

use App\UserModule\ValueObject\Email;
use App\UserModule\ValueObject\UserId;
use App\UserModule\ValueObject\UserName;
use JsonSerializable;

class User implements JsonSerializable {
	public function __construct(
		private readonly UserId $id,
		private readonly UserName $username,
		private readonly Email $email
	) {
	}

	public function getEmail(): Email {
		return $this->email;
	}

	public function getId(): UserId {
		return $this->id;
	}

	public function getUsername(): UserName {
		return $this->username;
	}

	public function jsonSerialize(): mixed {
		return [
			'id' => $this->id->getValue(),
			'username' => $this->username->getValue(),
			'email' => $this->email->getValue(),
		];
	}
}
