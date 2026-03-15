<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support\Database;

use Doctrine\DBAL\Exception;

/**
 * Wrapper class for Doctrine QueryBuilder with added functionality
 */
class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
	/**
	 * Get all rows in result
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get(): array
	{
		return $this->fetchAllAssociative();
	}

	/**
	 * Get first row in result
	 *
	 * @return array|false
	 * @throws Exception
	 */
	public function first(): array|false
	{
		return $this->fetchAssociative();
	}

	/**
	 * Get a single column value from the first row
	 *
	 * @param string $column
	 * @return mixed
	 * @throws Exception
	 */
	public function value(string $column): mixed
	{
		$row = $this->first();
		return $row ? $row[$column] ?? null : null;
	}

	/**
	 * Get a flat array of a single column across all rows
	 *
	 * @param string $column
	 * @return array
	 * @throws Exception
	 */
	public function pluck(string $column): array
	{
		return array_column($this->get(), $column);
	}

	/**
	 * Check if any rows exist
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function exists(): bool
	{
		return $this->first() !== false;
	}

	/**
	 * Get total row count
	 *
	 * @param string $column
	 * @return int
	 * @throws Exception
	 */
	public function count(string $column = '*'): int
	{
		$result = (clone $this)
			->select("COUNT($column) as count")
			->fetchAssociative();

		return (int) ($result['count'] ?? 0);
	}

	/**
	 * Paginate results — returns rows + pagination meta
	 *
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 * @throws Exception
	 */
	public function paginate(int $page, int $perPage = 15): array
	{
		$total = $this->count();

		$results = (clone $this)
			->setMaxResults($perPage)
			->setFirstResult(($page - 1) * $perPage)
			->get();

		return [
			'data'         => $results,
			'total'        => $total,
			'per_page'     => $perPage,
			'current_page' => $page,
			'last_page'    => (int) ceil($total / $perPage),
		];
	}

	/**
	 * Order by column shorthand asc
	 *
	 * @param string $column
	 * @return QueryBuilder
	 */
	public function orderByAsc(string $column): static
	{
		return $this->orderBy($column, 'ASC');
	}

	/**
	 * Order by column shorthand desc
	 *
	 * @param string $column
	 * @return $this
	 */
	public function orderByDesc(string $column): static
	{
		return $this->orderBy($column, 'DESC');
	}

	/**
	 * Conditionally apply a callback to the builder (useful for optional filters)
	 *
	 * @param bool $condition
	 * @param callable $callback
	 * @return QueryBuilder
	 */
	public function when(bool $condition, callable $callback): static
	{
		if ($condition) {
			$callback($this);
		}
		return $this;
	}

	/**
	 * Apply a callback, useful for extracting reusable query scopes
	 *
	 * @param callable $callback
	 * @return QueryBuilder
	 */
	public function tap(callable $callback): static
	{
		$callback($this);
		return $this;
	}
}