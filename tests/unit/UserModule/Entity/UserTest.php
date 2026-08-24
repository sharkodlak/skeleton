<?php

declare(strict_types = 1);

namespace Tests\Unit\UserModule\Entity;

use App\UserModule\Entity\User;
use App\UserModule\ValueObject\Email;
use App\UserModule\ValueObject\UserId;
use App\UserModule\ValueObject\UserName;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
	public function testGetId(): void {
		$userId = new UserId('123');
		$username = new UserName('test');
		$mail = new Email('test@test.example');
		$user = new User($userId, $username, $mail);
		$this->assertSame('123', $user->getId()->getValue());
	}
}
