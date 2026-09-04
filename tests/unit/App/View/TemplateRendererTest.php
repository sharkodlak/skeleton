<?php

declare(strict_types = 1);

namespace Tests\Unit\App\View;

use App\App\View\TemplateRenderer;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase {
	public function testItRendersTemplateWithVariables(): void {
		$renderer = new TemplateRenderer();

		$output = $renderer->render('home.php', [ 'message' => 'Hello world' ]);

		self::assertStringContainsString('Hello world', $output);
		self::assertStringContainsString('<html', $output);
	}
}
