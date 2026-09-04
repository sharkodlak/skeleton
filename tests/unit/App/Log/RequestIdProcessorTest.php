<?php

declare(strict_types = 1);

namespace Tests\Unit\App\Log;

use App\App\Log\RequestIdProcessor;
use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestIdProcessor::class)]
final class RequestIdProcessorTest extends TestCase {
	public function testItAddsTheRequestIdFromTheFastCgiParameter(): void {
		$record = (new RequestIdProcessor())($this->record());

		self::assertSame('abc123', $record->extra['request_id'] ?? null);
	}

	public function testItLeavesRecordsAloneWithoutARequestId(): void {
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		unset($_SERVER['HTTP_X_REQUEST_ID']);

		$record = (new RequestIdProcessor())($this->record());

		self::assertArrayNotHasKey('request_id', $record->extra);
	}

	protected function setUp(): void {
		parent::setUp();

		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		$_SERVER['HTTP_X_REQUEST_ID'] = 'abc123';
	}

	protected function tearDown(): void {
		// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
		unset($_SERVER['HTTP_X_REQUEST_ID']);

		parent::tearDown();
	}

	private function record(): LogRecord {
		return new LogRecord(new DateTimeImmutable(), 'App', Level::Info, 'probe');
	}
}
