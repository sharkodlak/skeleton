<?php

declare(strict_types = 1);

namespace App\App\Api;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\InvalidSecurityException;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidSecurity;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestValidator implements MiddlewareInterface {
	public function __construct(
		private readonly ServerRequestValidator $validator,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		try {
			$this->validator->validate($request);
		} catch (InvalidSecurity $e) {
			throw new InvalidSecurityException($e->getMessage());
		} catch (ValidationFailed $e) {
			throw new InvalidParameterException($e->getMessage());
		}

		return $handler->handle($request);
	}
}
