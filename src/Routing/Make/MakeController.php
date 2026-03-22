<?php

namespace MaplePHP\Core\Routing\Make;

use MaplePHP\Core\App;
use MaplePHP\Core\Console\ArgDefinition;
use MaplePHP\Core\Console\Generators\ClassGenerator;
use MaplePHP\Core\Routing\DefaultCommand;
use MaplePHP\Http\StreamFactory;

class MakeController extends DefaultCommand
{

	const MAKE = [
		'controller' => ['app/Controllers/', 'App\Controllers'],
		'service' => ['app/Services/', 'App\Services'],
		'migration' => ['database/migrations', 'Migrations'],
		'command' => ['app/Commands', 'App\Commands'],
	];

	/**
	 * Set unique command name to show in help titles.
	 *
	 * @return string
	 */
	public static function name(): string
	{
		return 'make';
	}

	/**
	 * Provides a short, human-readable description of the command.
	 *
	 * @return string
	 */
	public static function description(): string
	{
		return 'Generate a new class file from a stub';
	}

	/**
	 * Defines the expected input arguments for the command.
	 *
	 * @return array
	 */
	protected function args(): array
	{
		return [
			new ArgDefinition('type', 'Select which class type to generate', required: false),
			new ArgDefinition('name', 'Enter the class name to be created', required: false),
		];
	}

	/**
	 * The default action method executed when this controller is invoked by the router.
	 *
	 * @return void
	 */
	public function index(): void
	{
		$type = empty($this->args['type']) ?
			$this->selectType("Select which class type to generate")
			: $this->args['type'];

		$name = empty($this->args['name']) ?
			$this->command->readline("Enter class name") :
			$this->args['name'];

		$gen = $this->initClassGenerator();
		$gen->bindPrefixToType("migration", $gen->getDate());
		$gen->generate($type, $name);
		$filePath = $gen->getRelativeDir() . "/" . $gen->getFile();

		$this->dumpAutoload();

		$this->command->message("");
		$this->command->approve("The class $filePath has been successfully created.");
		$this->command->message("");
	}

	private function dumpAutoload(): void
	{
		$composerJson = App::get()->dir()->root() . '/composer.json';

		if (!file_exists($composerJson)) {
			return;
		}

		shell_exec('composer dump-autoload -o --working-dir=' . escapeshellarg(App::get()->dir()->root()));
	}

	private function selectType(string $title): string
	{
		$allowedTypes = array_keys(self::MAKE);
		$index = $this->command->select($title, $allowedTypes);
		return $allowedTypes[$index];
	}

	/**
	 * Init the class generator instance
	 *
	 * @return ClassGenerator
	 */
	private function initClassGenerator(): ClassGenerator
	{
		$stubDir = App::get()->coreDir() . "/Console/Stubs";
		$gen = new ClassGenerator($stubDir, App::get()->dir()->root(), new StreamFactory());
		foreach (self::MAKE as $type => $arr) {
			$gen->registerType($type, ...$arr);
		}
		return $gen;
	}
}