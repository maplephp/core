<?php

declare(strict_types=1);


namespace MaplePHP\Core\Render\Errors;

use MaplePHP\Core\App;
use MaplePHP\Core\Interfaces\ErrorPageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigErrorPage implements ErrorPageInterface
{
	public function render(
		ResponseInterface      $response,
		ServerRequestInterface $request,
		array                  $context = []
	): string
	{
		$loader = new FilesystemLoader(App::get()->dir()->resources() . '/errors');

		$twig = new Environment($loader, [
			'cache' => false,
		]);

		$configs = App::get()->configs();
		$configs = isset($configs['configs']) ? $configs['configs'] : ['app_title' => "MaplePHP"];

		$twig->addGlobal('app', $configs);

		return $twig->render('error.twig', [
			'code' => $response->getStatusCode(),
			'message' => $context['message'] ?? $response->getReasonPhrase(),
			'uri' => (string)$request->getUri(),
			'context' => $context,
		]);
	}
}
