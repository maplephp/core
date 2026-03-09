<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Serve;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use MaplePHP\Core\Routing\DefaultShellController;
use MaplePHP\Core\Support\Process\ServePHP;

class ServeController extends DefaultShellController
{

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

		if ($add->isHelp()) {
			$this->command->message('');
			$this->command->title('Unitary – PHP Server');
			$this->command->message('');
			$this->command->title('Start the PHP development server:');
			$this->command->statusMsg('./maple serve');
			$this->command->message('');
			$this->command->message('Start with custom host and port:');
			$this->command->statusMsg(
				'./maple serve --host=' . $add->getUri()->getHost() .
				' --port=' . $add->getUri()->getPort()
			);
			$this->command->message('');

		} else {
			$add->run('MaplePHP development server is now running:');
		}

		return $response;
	}

}
