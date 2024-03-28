<?php

declare(strict_types = 1);

namespace Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\ValueObject\Email;
use App\ValueObject\UserId;
use App\ValueObject\UserName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase {
	/** @var UserRepository&MockObject $userRepository */
	private UserRepository $userRepository;

	public function testFindByUserId(): void {
		$userId = new UserId('00000000-0000-0000-0000-000000000000');
		$username = new UserName('test');
		$email = new Email('test@test.example');
		$user = new User($userId, $username, $email);
		$this->userRepository
			->expects($this->once())
			->method('findUserById')
			->willReturn($user);
		
		$this->assertEquals($user, $this->userRepository->findUserById($userId->getValue()));
	}

	protected function setUp(): void {
		$this->userRepository = $this->createMock(UserRepository::class);
	}
}
