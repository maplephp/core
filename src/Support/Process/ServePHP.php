<?php

namespace MaplePHP\Core\Support\Process;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use MaplePHP\Prompts\Command;

class ServePHP
{
	private ServerRequestInterface|RequestInterface $request;
	private UriInterface $uri;
	private ProcessManager $proc;
	private Command $command;
	private array $args;

	public function __construct(ServerRequestInterface|RequestInterface $request, Command $command)
	{
		$this->request = $request;
		$this->proc = new ProcessManager();
		$this->command = $command;
		$this->args = $this->request->getCliArgs();

		$port = (int)($this->args['port'] ?? 8000);
		$host = $this->args['host'] ?? "localhost";
		$this->uri = $this->request->getUri()
			->withScheme("http")
			->withHost($host)
			->withPort($port);
	}

	/**
	 * Get URI instance
	 *
	 * @return UriInterface
	 */
	public function getUri(): UriInterface
	{
		return $this->uri;
	}

	/**
	 * Run PHP server
	 * @param string $serverTitle
	 * @return void
	 */
	public function run(string $serverTitle): void
	{
		$port = (int)($this->args['port'] ?? 8000);
		$host = $this->args['host'] ?? "localhost";
		$this->uri = $this->uri->withHost($host)->withPort($port);

		$this->command->message('');
		$this->command->message(
			$this->command->getAnsi()->style(
				['blue', 'bold'],
				$serverTitle
			)
		);
		$this->command->approve($this->uri->getUri());
		$this->command->message('');

		$publicPath = realpath($this->uri->getDir()) . "/public";
		$shellCommand = "php -S '" . $this->uri->getAuthority() . "' -t '" . $publicPath . "'";

		if ($this->proc->isSupported()) {
			$this->runProcess($shellCommand);
		} else {
			shell_exec($shellCommand);
		}
	}

	/**
	 * Add a process to run
	 *
	 * @param string $command
	 * @return void
	 */
	protected function runProcess(string $command): void
	{
		$this->proc->add("php", $command);
		$this->proc->stopSignal(function () {
			$this->command->message('');
			$this->command->message('');
			$this->command->message(
				$this->command->getAnsi()->yellow('Stopping server...')
			);
			$this->command->message('');
		});
		$this->proc->run();
		$this->proc->close();
	}
}