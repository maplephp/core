<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use RuntimeException;

class DB
{
	private static ?self $inst = null;
	private Connection $conn;

	private function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}

	/**
	 * Boot the singleton with a connection
	 *
	 * @param Connection $conn
	 */
	public static function boot(Connection $conn): void
	{
		self::$inst = new self($conn);
	}

	/**
	 * Get the raw Doctrine connection
	 *
	 * @return Connection
	 */
	public static function getConnection(): Connection
	{
		return self::instance()->conn;
	}

	/**
	 * Get a new QueryBuilder instance
	 *
	 * @return QueryBuilder
	 */
	public static function qb(): QueryBuilder
	{
		return new QueryBuilder(self::getConnection());
	}

	/**
	 * Start a query from a table
	 *
	 * @param string $table
	 * @return QueryBuilder
	 */
	public static function table(string $table, string $columns = "*"): QueryBuilder
	{
		return self::qb()->from($table)->select($columns);
	}

	/**
	 * Inserts a table row with specified data.
	 *
	 * @param string $table
	 * @param array $set
	 * @return int|string
	 * @throws Exception
	 */
	public static function insert(string $table, array $set): int|string
	{
		return DB::getConnection()->insert($table, $set);
	}

	/**
	 * Executes an SQL UPDATE statement on a table.
	 *
	 * @param string $table
	 * @param array $set
	 * @param array $where
	 * @return int|string
	 * @throws Exception
	 */
	public static function update(string $table, array $set, array $where = []): int|string
	{
		return DB::getConnection()->update($table, $set, $where);
	}

	/**
	 * Executes an SQL DELETE statement on a table.
	 * 
	 * @param string $table
	 * @param array $set
	 * @param array $where
	 * @return int|string
	 * @throws Exception
	 */
	public static function delete(string $table, array $set, array $where = []): int|string
	{
		return DB::getConnection()->delete($table, $set, $where);
	}
	
	/**
	 * Creates a SchemaManager that can be used to inspect or change the
	 * database schema through the connection.
	 * @return AbstractSchemaManager
	 * @throws Exception
	 */
	public static function schema(): AbstractSchemaManager
	{
		return DB::getConnection()->createSchemaManager();
	}

	/**
	 * Execute a raw SQL query and return all rows
	 *
	 * @param string $sql
	 * @param array $params
	 * @return array
	 * @throws Exception
	 */
	public static function select(string $sql, array $params = []): array
	{
		return self::getConnection()->fetchAllAssociative($sql, $params);
	}

	/**
	 * Execute a raw SQL statement (INSERT, UPDATE, DELETE)
	 * Returns the number of affected rows
	 *
	 * @param string $sql
	 * @param array $params
	 * @return int
	 * @throws Exception
	 */
	public static function statement(string $sql, array $params = []): int
	{
		return self::getConnection()->executeStatement($sql, $params);
	}

	/**
	 * Get the last inserted ID
	 *
	 * @return string|int
	 * @throws Exception
	 */
	public static function lastInsertId(): string|int
	{
		return self::getConnection()->lastInsertId();
	}

	/**
	 * Run a callback inside a transaction
	 *
	 * @param callable $callback
	 * @return mixed
	 * @throws Exception
	 * @throws \Throwable
	 */
	public static function transaction(callable $callback): mixed
	{
		$conn = self::getConnection();
		$conn->beginTransaction();

		try {
			$result = $callback($conn);
			$conn->commit();
			return $result;
		} catch (\Throwable $e) {
			$conn->rollBack();
			throw $e;
		}
	}

	/**
	 * Resolve the singleton instance
	 *
	 * @return DB
	 */
	public static function instance(): self
	{
		if (self::$inst === null) {
			throw new RuntimeException('DB has not been booted. Call DB::boot() first.');
		}
		return self::$inst;
	}
}