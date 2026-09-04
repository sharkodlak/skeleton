<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
	// Phinx lives in require-dev, and so do the migrations and seeds that extend it.
	->addPathToScan(__DIR__ . '/db', isDev: true)
	->ignoreErrorsOnPackage('nyholm/psr7-server', [ErrorType::UNUSED_DEPENDENCY])
	// Symfony\Component\HttpKernel\Kernel extends AbstractKernel from the
	// dependency-injection component, so it is needed at runtime even though
	// nothing here references the component directly.
	->ignoreErrorsOnPackage('symfony/dependency-injection', [ErrorType::UNUSED_DEPENDENCY])
;