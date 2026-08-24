<?php

declare(strict_types = 1);

namespace App\UserModule\Dto;

use JsonSerializable;

class UserCheckDto implements JsonSerializable {
	public function __construct(
		private ?bool $emailAvailable = null,
		private ?bool $usernameAvailable = null
	) {
	}

	public function isEmailAvailable(): ?bool {
		return $this->emailAvailable;
	}

	public function isUsernameAvailable(): ?bool {
		return $this->usernameAvailable;
	}

	public function isUsed(): bool {
		return $this->emailAvailable === false || $this->usernameAvailable === false;
	}

	public function setEmailAvailable(bool $available): void {
		$this->emailAvailable = $available;
	}

	public function setUsernameAvailable(bool $available): void {
		$this->usernameAvailable = $available;
	}

	public function jsonSerialize(): mixed {
		$data = [];

		if ($this->emailAvailable !== null) {
			$data['email'] = $this->availabilityString($this->emailAvailable);
		}

		if ($this->usernameAvailable !== null) {
			$data['username'] = $this->availabilityString($this->usernameAvailable);
		}

		return $data;
	}

	private function availabilityString(bool $available): string {
		return $available ? 'available' : 'used';
	}
}
