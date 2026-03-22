<?php

namespace MaplePHP\Core\Providers;

use MaplePHP\Core\Console\CommandRegistry;
use MaplePHP\Core\Console\Commands\MakeCommand;
use MaplePHP\Core\Support\ServiceProvider;
use Psr\Container\ContainerInterface;

class CommandProvider extends ServiceProvider
{

	protected const COMMANDS = [];

	private array $commands = [
		MakeCommand::class,
	];

	/**
	 * Just a test service provider
	 *
	 * @param ContainerInterface $container
	 * @return void
	 */
	public function register(ContainerInterface $container): void
	{
		$commands = array_merge($this->commands, static::COMMANDS);
		CommandRegistry::register($commands);
	}
}