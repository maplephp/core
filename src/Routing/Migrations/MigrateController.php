<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Migrations;

use MaplePHP\Core\Console\ArgDefinition;
use MaplePHP\Core\Support\Database\MigrationTracker;
use MaplePHP\DTO\Format\Str;
use MaplePHP\DTO\Traverse;
use MaplePHP\Http\StreamFactory;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use MaplePHP\Core\App;
use MaplePHP\Core\Support\Database\Migrations;
use MaplePHP\Core\Routing\DefaultCommand;
use MaplePHP\Core\Support\Database\DB;

class MigrateController extends DefaultCommand
{
	private ?AbstractSchemaManager $schemaManager = null;
	private ?MigrationTracker $tracker = null;

	public static function name(): string
	{
		return 'migrate';
	}

	public static function description(): string
	{
		return 'Create database migrations';
	}

	protected function args(): array
	{
		return [
			new ArgDefinition('name', 'Run only one migration by name (always re-runs even if already migrated)'),
		];
	}

	public function index(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm($this->confirmMessage("UP"))) {
			return $response;
		}

		$this->runDirection('up');

		return $response;
	}

	public function up(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm($this->confirmMessage("UP"))) {
			return $response;
		}

		$this->runDirection('up');

		return $response;
	}

	public function down(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm($this->confirmMessage("DOWN"))) {
			return $response;
		}

		$last = $this->getTracker()->last();

		if ($last === null) {
			$this->command->message("Nothing to roll back.");
			return $response;
		}

		$file = App::get()->dir()->migrations() . '/' . $last['file'];
		$this->runMigration($file, 'down', force: true);

		return $response;
	}

	public function fresh(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to refresh migration?")) {
			return $response;
		}

		$this->runDirection('down');
		$this->runDirection('up');

		return $response;
	}

	public function clear(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to clear migration?")) {
			return $response;
		}

		$this->runDirection('down');

		return $response;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	private function confirmMessage(string $direction): string
	{
		if (isset($this->args['name'])) {
			return "Are you sure you want to run {$this->args['name']} migration $direction?";
		}

		return "Are you sure you want to run all available migration $direction?";
	}

	private function runDirection(string $direction): void
	{
		$name  = !empty($this->args['name'])
			? Str::value($this->args['name'])->toLower()->get()
			: null;

		$files = $this->getMigrationFiles();

		if ($name !== null) {
			$files = array_filter(
				$files,
				fn(string $file) => Str::value(pathinfo($file, PATHINFO_FILENAME))->toLower()->get() === $name
			);

			if (empty($files)) {
				throw new RuntimeException("No migration file found matching \"$name\".");
			}
		}

		foreach ($files as $file) {
			$this->runMigration($file, $direction, force: $name !== null);
		}
	}

	private function runMigration(string $file, string $direction, bool $force = false): void
	{
		$tracker = $this->getTracker();

		if (!$force && $tracker->has($file, $direction)) {
			$this->command->message(
				$this->command->getAnsi()->grey("Skipping already migrated: " . basename($file))
			);
			return;
		}

		$segments = Traverse::value(explode('_', pathinfo($file, PATHINFO_FILENAME)));
		$class    = $segments->last()->strUcFirst()->get();

		require_once $file;

		$fqcn = '\\Migrations\\' . $class;

		if (!class_exists($fqcn)) {
			throw new RuntimeException("Migration class $fqcn does not exist.");
		}

		$inst = new $fqcn();
		if (!($inst instanceof Migrations)) {
			throw new RuntimeException("Migration class must extend: " . Migrations::class);
		}

		$schemaManager = $this->getSchemaManager();
		$currentSchema = $schemaManager->introspectSchema();
		$targetSchema  = clone $currentSchema;

		$inst->{$direction}($targetSchema);
		$this->executeSchema($currentSchema, $targetSchema);

		if ($direction === 'up') {
			$tracker->add($file, $class, $direction);
		} else {
			$tracker->remove($file, 'up');
		}

		$label = Str::value($direction)->toUpper()->get();
		$this->command->approve("Executed migration $label: $fqcn");
	}

	private function executeSchema(Schema $fromSchema, Schema $toSchema): void
	{
		$connection = DB::getConnection();
		$diff       = $this->getSchemaManager()->createComparator()->compareSchemas($fromSchema, $toSchema);
		$statements = $connection->getDatabasePlatform()->getAlterSchemaSQL($diff);

		if (empty($statements)) {
			return;
		}

		foreach ($statements as $sql) {
			if (isset($this->args['read'])) {
				$this->command->message($sql);
			} else {
				$connection->executeStatement($sql);
			}
		}
	}

	private function getSchemaManager(): AbstractSchemaManager
	{
		if ($this->schemaManager === null) {
			$this->schemaManager = DB::getConnection()->createSchemaManager();
		}

		return $this->schemaManager;
	}

	private function getTracker(): MigrationTracker
	{
		if ($this->tracker === null) {
			$this->tracker = new MigrationTracker(
				App::get()->dir()->migrations(),
				new StreamFactory()
			);
		}

		return $this->tracker;
	}

	private function getMigrationFiles(): array
	{
		$migDir = App::get()->dir()->migrations();

		if (!is_dir($migDir)) {
			mkdir($migDir, 0755, true);
		}

		$files = glob($migDir . "/*.php") ?: [];
		sort($files);

		return $files;
	}

	private function confirm(string $question): bool
	{
		if ($this->command->confirm($question)) {
			return true;
		}

		$this->command->message("");
		$this->command->message($this->command->getAnsi()->yellow("Aborting migrations..."));
		$this->command->message("");

		return false;
	}
}