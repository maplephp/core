<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing;

use MaplePHP\Core\Console\ArgDefinition;
use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use MaplePHP\Prompts\Command;
use MaplePHP\Prompts\Themes\Blocks;
use MaplePHP\Validate\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class DefaultCommand
{
    protected readonly ServerRequestInterface|RequestInterface $request;
    protected readonly ContainerInterface $container;
    protected Command $command;
    protected array $config;
    protected array $args;
    protected ?ConfigPropsInterface $props = null;
    protected string|bool $path;

    /**
     * Set some data type safe object that comes from container and the dispatcher
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ErrorException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->args = $this->container->get("args");
        $this->props = $this->container->get("props");
        $this->command = $this->container->get("command");
        $this->request = $this->container->get("request");
        $this->config = $this->container->get("config");
    }

	/**
	 * Add command name
	 *
	 * @return string
	 */
	abstract protected static function name(): string;

	/**
	 * Describe what the command is and does
	 *
	 * @return string
	 */
	abstract protected static function description(): string;


	/**
	 * Declare accepted args . return an array of ArgDefinition
	 * Empty array means the command accepts no options
	 *
	 * @return array
	 */
	protected function args(): array
	{
		return [];
	}

	/**
	 * Handle command
	 *
	 * @return int
	 */
	public function handle(): int
	{
		if ($this->isMissingRequired()) {
			$this->renderHelp();
			return 1;
		}
		return 0;
	}

	/**
	 * Create help message
	 *
	 * @return void
	 */
	private function renderHelp(): void
	{
		$args = $this->args();
		$blocks = new Blocks($this->command);
		$this->command->message("");

		$blocks->addHeadline("--- MaplePHP Help ---", "green");

		$blocks->addSection("Usage", function (Blocks $inst) {
			return $inst
				->addExamples(
					"./maple " . static::name() . " [options]",
					static::description()
				);
		});

		if($args !== []) {
			$blocks->addSection("Arguments", function (Blocks $inst) use ($args) {
				foreach ($args as $arg) {
					$required = " " . $this->command->getAnsi()->italic($arg->required ? "(required)" : "(optional)");
					$inst = $inst
						->addOption("--" . $arg->name . $required, $arg->description);
				}
				return $inst;
			});
		}
		$this->command->message("");
	}

	/**
	 * Validate args
	 *
	 * @return bool
	 */
	private function isMissingRequired(): bool
	{
		if(isset($this->args['help'])) {
			return true;
		}
		foreach (array_values(array_filter($this->args(), fn($a) => $a->required)) as $i => $arg) {
			if (!isset($this->args[$arg->name])) {
				return true;
			}
		}

		return false;
	}
}
