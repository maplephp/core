<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Serve;

use MaplePHP\Core\Console\ArgDefinition;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use MaplePHP\Core\Routing\DefaultCommand;
use MaplePHP\Core\Support\Process\ServePHP;

class ServeController extends DefaultCommand
{

	public static function name(): string
	{
		return 'serve';
	}

	public static function description(): string
	{
		return 'Run development server';
	}

	protected function args(): array
	{
		return [
			new ArgDefinition('host', 'Set custom host, e.g. 127.0.0.1'),
			new ArgDefinition('port', 'Set custom port, e.g. 8888'),
		];
	}

	/**
	 * A simple PHP server
	 *
	 * @param ResponseInterface $response
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 */
	public function index(ResponseInterface $response, RequestInterface $request): ResponseInterface
	{
		$add = new ServePHP($request, $this->command);
		$add->run('MaplePHP development server is now running:');
		return $response;
	}

}
