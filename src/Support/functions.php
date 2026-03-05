<?php


use MaplePHP\Core\Exceptions\HttpException;

function abort(int $code, string $message = '', array $props = []): void {
    throw new HttpException($code, $message, $props);
}