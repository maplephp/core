<?php

declare(strict_types=1);

namespace MaplePHP\Core\Providers;

use MaplePHP\Core\App;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\DriverManager;
use MaplePHP\Core\Support\ServiceProvider;
use MaplePHP\Core\Support\Database\DB;
use RuntimeException;

class DatabaseProvider extends ServiceProvider
{
	private ContainerInterface $container;

	/**
	 * Register a database connection
	 */
	public function register(ContainerInterface $container): void
	{
		$this->container = $container;
		$config = App::get()->configs();
		$default = ($config['database']['default'] ?? null);
		if (!empty($default)) {
			$dbConfig = $this->resolveConnection($config);
			$conn = DriverManager::getConnection($dbConfig);
			$container->set("db.connection", $conn);
		}
	}

	/**
	 * Pass connection to DB helper class in boot
	 */
	public function boot(): void
	{
		if ($this->container->has("db.connection")) {
			DB::boot($this->container->get("db.connection"));
		}
	}

	/**
	 * Resolve the correct connection config based on the default driver
	 */
	private function resolveConnection(array $config): array
	{
		$database = $config['database'];
		$default = $database['default'];
		$connections = $database['connections'] ?? [];
		
		if (!isset($connections[$default])) {
			throw new RuntimeException("Database connection '{$default}' is not defined in config.");
		}

		$dbConfig = $connections[$default];

		if ($dbConfig['driver'] === 'pdo_sqlite' && !($dbConfig['memory'] ?? false)) {
			$dbConfig = $this->resolveSQLitePath($dbConfig, $config['dir']);
		}

		return $dbConfig;
	}

	/**
	 * Resolve and touch the SQLite file, returning updated config
	 */
	private function resolveSQLitePath(array $dbConfig, string $baseDir): array
	{
		$dbDir = rtrim($baseDir, '/') . '/database';
		$basename = basename($dbConfig['file'] ?? 'database.sqlite');
		$dbPath = $dbDir . '/' . $basename;
		$extention = pathinfo($basename, PATHINFO_EXTENSION);

		if ($extention !== "sqlite") {
			throw new RuntimeException("The SQLite file ($basename) is missing the extension '.sqlite'.");
		}

		if (!is_file($dbPath)) {
			if (!is_dir($dbDir) && !mkdir($dbDir, 0755, true)) {
				throw new RuntimeException("Failed to create database directory: $dbDir");
			}
			if (file_put_contents($dbPath, '') === false) {
				throw new RuntimeException("Failed to create SQLite database file: $dbPath");
			}
		}

		$dbConfig['path'] = $dbPath;
		unset($dbConfig['file']);

		return $dbConfig;
	}
}