<?php

declare(strict_types = 1);

namespace App\App\Fw\Symfony;

use Symfony\Component\Yaml\Yaml;

final class RouteConfig {
	/** @return array<string, array{path: string}> */
	public static function all(): array {
		$config = Yaml::parseFile(__DIR__ . '/config/routes.yaml');

		if (!\is_array($config)) {
			return [];
		}

		/** @var array<string, array{path?: string}> $routeConfig */
		$routeConfig = $config['routes'] ?? [];

		if (!\is_array($routeConfig)) {
			return [];
		}

		return self::normalize($routeConfig);
	}

	public static function matchesPath(string $path): bool {
		foreach (self::all() as $route) {
			if ($route['path'] === $path) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<string, array{path?: string}> $routes
	 * @return array<string, array{path: string}>
	 */
	private static function normalize(array $routes): array {
		$normalized = [];

		foreach ($routes as $name => $definition) {
			if (!\is_array($definition) || !\is_string($definition['path'] ?? null)) {
				continue;
			}

			$normalized[(string) $name] = [ 'path' => $definition['path'] ];
		}

		return $normalized;
	}
}
