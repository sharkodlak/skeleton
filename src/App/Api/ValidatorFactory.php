<?php

declare(strict_types = 1);

namespace App\App\Api;

use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ValidatorFactory {
	private readonly ValidatorBuilder $validatorBuilder;

	public function __construct(
		private readonly string $openApiYamlFile,
		?string $cacheDir = null,
	) {
		$builder = (new ValidatorBuilder())->fromYamlFile($this->openApiYamlFile);

		if ($cacheDir !== null) {
			$builder->setCache(new FilesystemAdapter(namespace: '', defaultLifetime: 0, directory: $cacheDir));
		}

		$this->validatorBuilder = $builder;
	}

	public function createRequestValidator(): RequestValidator {
		$validator = $this->validatorBuilder->getServerRequestValidator();
		return new RequestValidator($validator);
	}

	public function createResponseValidator(): ResponseValidator {
		$validator = $this->validatorBuilder->getResponseValidator();
		return new ResponseValidator($validator);
	}
}
