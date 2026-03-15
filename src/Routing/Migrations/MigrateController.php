<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Migrations;

use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use MaplePHP\Core\App;
use MaplePHP\Core\Support\Database\Migrations;
use MaplePHP\Core\Routing\DefaultShellController;
use MaplePHP\Core\Support\Database\DB;

class MigrateController extends DefaultShellController
{
	private ?AbstractSchemaManager $schemaManager = null;
	
	/**
	 * Run ALL migrations (up), or a single one if --name is provided.
	 */
	public function index(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to run ALL migrations?")) {
			return $response;
		}

		$this->runDirection('up');

		return $response;
	}

	/**
	 * Run migrations up, or a single one if --name is provided.
	 */
	public function up(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to run migration UP?")) {
			return $response;
		}

		$this->runDirection('up');

		return $response;
	}

	/**
	 * Run migrations down, or a single one if --name is provided.
	 */
	public function down(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to run migration DOWN?")) {
			return $response;
		}

		$this->runDirection('down');

		return $response;
	}

	/**
	 * Roll back and re-run migrations (down + up), or a single one if --name is provided.
	 */
	public function fresh(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to refresh migration?")) {
			return $response;
		}

		$this->runDirection('down');
		$this->runDirection('up');

		return $response;
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Run all migration files in the given direction, or only the one
	 * matching --name if supplied.
	 */
	private function runDirection(string $direction): void
	{
		$name  = !empty($this->args['name']) ? strtolower($this->args['name']) : null;
		$files = $this->getMigrationFiles();

		if ($name !== null) {
			$files = array_filter($files, fn(string $file) => strtolower(pathinfo($file, PATHINFO_FILENAME)) === $name);

			if (empty($files)) {
				throw new RuntimeException("No migration file found matching \"$name\".");
			}
		}

		foreach ($files as $file) {
			$this->runMigration($file, $direction);
		}
	}

	/**
	 * Instantiate, execute, and persist a single migration file.
	 */
	private function runMigration(string $file, string $direction): void
	{
		$base  = pathinfo($file, PATHINFO_FILENAME);
		$class = "\\Migrations\\" . ucfirst($base);

		if (!class_exists($class)) {
			throw new RuntimeException("Migration class $class does not exist.");
		}

		$inst = new $class();
		if (!($inst instanceof Migrations)) {
			throw new RuntimeException("Migration class must extend: " . Migrations::class);
		}

		$schemaManager = $this->getSchemaManager();
		$currentSchema = $schemaManager->introspectSchema();
		$targetSchema  = clone $currentSchema;

		$inst->{$direction}($targetSchema);

		$this->executeSchema($currentSchema, $targetSchema);

		$label = strtoupper($direction);
		$this->command->approve("Executed migration $label: $class");
	}

	/**
	 * Diff two schemas and execute the resulting SQL, if any.
	 */
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

	/**
	 * Lazy-load and cache the schema manager.
	 */
	private function getSchemaManager(): AbstractSchemaManager
	{
		if ($this->schemaManager === null) {
			$this->schemaManager = DB::getConnection()->createSchemaManager();
		}

		return $this->schemaManager;
	}

	/**
	 * Return all migration files, sorted for deterministic order.
	 */
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

	/**
	 * Show a confirmation prompt and abort with a message if declined.
	 */
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