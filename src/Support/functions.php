<?php

declare(strict_types=1);

use MaplePHP\Core\Exceptions\HttpException;

/**
 * The right way to abort scripts in Maple
 *
 * @param int $code
 * @param string $message
 * @param array $props
 * @return void
 */
function abort(int $code, string $message = '', array $props = []): void {
    throw new HttpException($code, $message, $props);
}

/**
 * The best way to get env param
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
	$value = \MaplePHP\Http\Env::getFromRegistry($key);

	if ($value === null) {
		return $default;
	}

	return match (strtolower((string)$value)) {
		'true'  => true,
		'false' => false,
		'null'  => null,
		'empty' => '',
		default => $value
	};
}