<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support\Process;

use Closure;
use RuntimeException;

class ProcessManager
{
	/**
	 * @var array<string,array{process:resource,pipes:array<int,resource>,suppress:bool,command:string}>
	 */
	private array $procArr = [];

	private ?Closure $stopClosure = null;
	private bool $signalRegistered = false;

	public function __construct()
	{
		if (function_exists('pcntl_async_signals')) {
			pcntl_async_signals(true);
		}
	}

	/**
	 * Check if the current environment supports this process handler.
	 *
	 * @return bool
	 */
	public function isSupported(): bool
	{
		return PHP_OS_FAMILY !== 'Windows'
			&& function_exists('proc_open')
			&& function_exists('proc_get_status')
			&& function_exists('proc_close')
			&& function_exists('pcntl_signal')
			&& function_exists('posix_kill');
	}

	/**
	 * Add a command to the process batch.
	 *
	 * @param string $key
	 * @param string $command
	 * @param bool $suppress
	 * @return void
	 */
	public function add(string $key, string $command, bool $suppress = false): void
	{
		if (!$this->isSupported()) {
			throw new RuntimeException('Process handling is not supported in this environment.');
		}

		$command = trim($command);

		if ($command === '') {
			throw new RuntimeException('Command can not be empty.');
		}

		if (isset($this->procArr[$key])) {
			throw new RuntimeException(sprintf('Process key "%s" already exists.', $key));
		}

		$descriptors = $suppress
			? [
				0 => ['pipe', 'r'],
				1 => ['file', '/dev/null', 'w'],
				2 => ['file', '/dev/null', 'w'],
			]
			: [
				0 => STDIN,
				1 => STDOUT,
				2 => STDERR,
			];

		$pipes = [];
		$process = proc_open($command, $descriptors, $pipes);

		if (!is_resource($process)) {
			throw new RuntimeException(sprintf('Failed to open process for command: %s', $command));
		}

		$this->procArr[$key] = [
			'process' => $process,
			'pipes' => $pipes,
			'suppress' => $suppress,
			'command' => $command,
		];

		if (!$this->signalRegistered) {
			$this->registerSignalHandler();
			$this->signalRegistered = true;
		}
	}

	/**
	 * Register a callback executed before shutdown on SIGINT.
	 *
	 * @param Closure $call
	 * @return $this
	 */
	public function stopSignal(Closure $call): self
	{
		$this->stopClosure = $call;
		return $this;
	}

	/**
	 * Returns true if any process is still running.
	 *
	 * @return bool
	 */
	public function running(): bool
	{
		foreach ($this->procArr as $proc) {
			if (!is_resource($proc['process'])) {
				continue;
			}

			$status = proc_get_status($proc['process']);

			if ($status['running']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get count of prepared processes.
	 *
	 * @return int
	 */
	public function hasProcesses(): int
	{
		return count($this->procArr);
	}

	/**
	 * Run loop until all processes have stopped.
	 *
	 * Callback signature:
	 * function (string $event, array $proc, string $key, array $status): void
	 *
	 * @param callable|null $call
	 * @return void
	 */
	public function run(?callable $call = null): void
	{
		while ($this->running()) {
			foreach ($this->procArr as $key => &$proc) {
				if (!is_resource($proc['process'])) {
					continue;
				}

				$status = proc_get_status($proc['process']);
				$proc['status'] = $status;

				if ($status['running']) {
					if ($call !== null) {
						$call('running', $proc, $key, $status);
					}

					continue;
				}

				if ($call !== null) {
					$call('stopped', $proc, $key, $status);
				}
			}

			usleep(500000);
		}
	}

	/**
	 * Close all processes and pipes.
	 *
	 * @return void
	 */
	public function close(): void
	{
		foreach ($this->procArr as &$proc) {
			foreach ($proc['pipes'] as $pipe) {
				if (is_resource($pipe)) {
					fclose($pipe);
				}
			}

			if (is_resource($proc['process'])) {
				proc_close($proc['process']);
			}
		}
	}

	/**
	 * Register SIGINT handler for graceful shutdown.
	 *
	 * @return void
	 */
	protected function registerSignalHandler(): void
	{
		pcntl_signal(SIGINT, function (): void {
			if ($this->stopClosure !== null) {
				($this->stopClosure)($this->procArr);
			}

			$this->terminateAll(SIGTERM);
			$this->close();

			exit(0);
		});
	}

	/**
	 * Send a signal to all running child processes.
	 *
	 * @param int $signal
	 * @return void
	 */
	protected function terminateAll(int $signal = SIGTERM): void
	{
		foreach ($this->procArr as $proc) {
			if (!is_resource($proc['process'])) {
				continue;
			}

			$status = proc_get_status($proc['process']);

			if (!empty($status['running']) && isset($status['pid'])) {
				posix_kill($status['pid'], $signal);
			}
		}
	}
}
