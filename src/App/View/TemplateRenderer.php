<?php

declare(strict_types = 1);

namespace App\App\View;

final class TemplateRenderer {
	/** @param array<string, string|int|float|bool|\Stringable|null> $variables */
	public function render(string $template, array $variables = []): string {
		\extract($variables, \EXTR_SKIP);
		\ob_start();
		require __DIR__ . '/../../templates/' . \ltrim($template, '/');
		return (string) \ob_get_clean();
	}
}
