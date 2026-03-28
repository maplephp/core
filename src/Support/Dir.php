<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support;

class Dir
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Get the root directory
     *
     * @return string
     */
    public function root(): string
    {
        return $this->dir;
    }

    /**
     * Get the public directory
     *
     * @return string
     */
    public function public(): string
    {
        return $this->dir . "/public";
    }

	/**
	 * Get the public directory
	 *
	 * @return string
	 */
	public function resources(): string
	{
		return $this->dir . "/resources";
	}

    /**
     * Get the routers directory
     *
     * @return string
     */

	public function routes(): string
	{
		return $this->dir . "/routes";
	}

    public function routers(): string
    {
        return $this->routes();
    }

    /**
     * Get the configs directory
     *
     * @return string
     */
    public function configs(): string
    {
        return $this->dir . "/configs";
    }

    /**
     * Get the logs directory
     *
     * @return string
     */
    public function logs(): string
    {
        return $this->dir . "/logs";
    }

    /**
     * Get the logs directory
     *
     * @return string
     */
    public function cache(): string
    {
        return $this->dir . "/storage/cache";
    }

    /**
     * Get the logs directory
     *
     * @return string
     */
    public function cacheFramework(): string
    {
        return $this->dir . "/storage/cache/framework";
    }

    /**
     * Get the app directory
     *
     * @return string
     */
    public function app(): string
    {
        return $this->dir . "/app";
    }

	/**
	 * Get miggration dir
	 * 
	 * @return string
	 */
	public function migrations(): string
	{
		return $this->dir . "/database/migrations";
	}
}
