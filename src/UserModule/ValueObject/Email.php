<?php

declare(strict_types = 1);

namespace App\UserModule\ValueObject;

class Email {
	public function __construct(
		private readonly string $value
	) {
	}

	public function __toString(): string {
		return $this->value;
	}

	public function getValue(): string {
		return $this->value;
	}
}
