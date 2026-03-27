<?php

declare(strict_types=1);

namespace MaplePHP\Core;

use MaplePHP\Core\Support\Dir;
use MaplePHP\Core\Enums\Environment;
use MaplePHP\Emitron\Contracts\AppInterface;

final class App implements AppInterface
{
    private static ?self $inst = null;
    private Dir $dir;
    private string $coreDir;
	private array $config;

	private function __construct(Dir $dir, array $config = []) {
        $this->dir = $dir;
        $this->coreDir = __DIR__;
		$this->config = $config;
    }

	/**
	 * This is a single to set App globals
	 *
	 * @param Dir $dir
	 * @param array $config
	 * @return self
	 */
    public static function boot(Dir $dir, array $config = []): self
    {
        if (self::$inst !== null) {
            throw new \RuntimeException('App already initialized.');
        }

        return self::$inst = new self($dir, $config);
    }

    /**
     * Get App singleton instance
     *
     * @return self
     */
    public static function get(): self
    {
        if (self::$inst === null) {
            throw new \RuntimeException('App not initialized. Call App::boot() first.');
        }

        return self::$inst;
    }

	/**
	 * Check if the environment is in prod
	 *
	 * @return bool
	 */
	public function isProd(): bool
	{
		return ($this->config['configs']['env'] ?? "development") === Environment::PROD->name();
	}

	/**
	 * Check if the environment is in stage
	 *
	 * @return bool
	 */
	public function isStage(): bool
	{
		return ($this->config['configs']['env'] ?? "development") === Environment::STAGE->name();
	}

	/**
	 * Check if the environment is in test
	 *
	 * @return bool
	 */
	public function isTest(): bool
	{
		return ($this->config['configs']['env'] ?? "development") === Environment::TEST->name();
	}

	/**
	 * Check if the environment is in dev
	 *
	 * @return bool
	 */
	public function isDev(): bool
	{
		return ($this->config['configs']['env'] ?? "development") === Environment::DEV->name();
	}

	/**
	 * Get current Environment
	 *
	 * @return string
	 */
	public function env(): string
	{
		return ($this->config['configs']['env'] ?? "development") ?? Environment::PROD->name();
	}

	/**
	 * Get core/boot dir where code app boot originate
	 *
	 * @return string
	 */
    public function coreDir(): string
    {
        return $this->coreDir;
    }

    /**
     * Get the app core Dir instance
     *
     * @return Dir
     */
    public function dir(): Dir
    {
        return $this->dir;
    }

	/**
	 * Get the app core configs
	 *
	 * @return array
	 */
	public function configs(): array
	{
		return $this->config;
	}
}