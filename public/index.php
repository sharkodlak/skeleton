<?php

declare(strict_types=1);

use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

$container = Bootstrap::boot();

printf("Skeleton booted successfully. Framework-specific entrypoint belongs in a dedicated branch.\n");

unset($container);
