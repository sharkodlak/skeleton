<?php

declare(strict_types = 1);

use App\App\Fw\Symfony\Kernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();
$response = (new Kernel())->handle($request);
$response->send();
