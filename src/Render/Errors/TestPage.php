<?php

declare(strict_types=1);

namespace MaplePHP\Core\Render\Errors;

use MaplePHP\Core\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MaplePHP\Core\Interfaces\ErrorPageInterface;

class TestPage implements ErrorPageInterface
{

    public function render(
        ResponseInterface $response,
        ServerRequestInterface $request,
        array $context = []
    ): string {
       return "Test Page ";
    }

	public function test(): string {
		return "Test Page 2";
	}
}