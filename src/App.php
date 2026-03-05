<?php

namespace MaplePHP\Core;


use MaplePHP\Core\Support\Dir;
use MaplePHP\Http\Interfaces\PathInterface;

final class App
{

    private static ?self $inst = null;
    private Dir $dir;
    private PathInterface $path;
    private string $coreDir;

    private function __construct(Dir $dir, PathInterface $path) {
        $this->dir = $dir;
        $this->path = $path;
        $this->coreDir = __DIR__;
    }

    /**
     * This is a single to set App globals
     *
     * @param Dir $dir
     * @param PathInterface $path
     * @return self
     */
    public static function boot(Dir $dir, PathInterface $path): self
    {
        if (self::$inst !== null) {
            throw new \RuntimeException('App already initialized.');
        }

        return self::$inst = new self($dir, $path);
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
     * Get the app core Request instance
     *
     * @return PathInterface
     */
    public function path(): PathInterface
    {
        return $this->path;
    }
}