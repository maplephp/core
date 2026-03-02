<?php

namespace MaplePHP\Core\Configs;

use Exception;
use MaplePHP\Http\Env;

class LoadConfigFiles
{
    private array $data = [];

    /**
     * Check if config parameter exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Add one config parameter
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add(string $key, mixed $value): self
    {
        if($this->has($key)) {
            throw new \InvalidArgumentException('Config parameter "' . $key . '" already exists.');
        }
        $inst = clone $this;
        $inst->data[$key] = $value;
        return $inst;
    }

    /**
     * Immutable load config from PHP file
     *
     * @param string $filePath
     * @return $this
     * @throws Exception
     */
    public function loadFile(string $filePath): self
    {
        if (!$this->hasExt($filePath, "php")) {
            throw new \InvalidArgumentException("The file '$filePath' is not a valid PHP file extension");
        }

        $inst = clone $this;
        $inst->data = array_merge($inst->data, $this->loadConfigFile($filePath));
        return $inst;
    }

    /**
     * Immutable load config from env file
     *
     * @param string $filePath
     * @return $this
     */
    public function loadEnvFile(string $filePath): self
    {
        if (!$this->hasExt($filePath, "env")) {
            throw new \InvalidArgumentException("The file '$filePath' is not a valid env file extension");
        }

        $inst = clone $this;
        $env = new Env($filePath);
        $env->execute();
        //$inst->data = array_merge($inst->data, $env->getData());
        return $inst;
    }

    /**
     * Creates a configuration with cache
     *
     * @return array
     */
    public function fetch(): array
    {
        return $this->data;
    }

    private function hasExt(string $filePath, string $extension): bool
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $ext === strtolower($extension);
    }

    /**
     * Load a config file that returns a array
     *
     * @param string $path
     * @return array
     * @throws Exception
     */
    protected function loadConfigFile(string $path): array
    {
        $path = realpath($path);

        if ($path === false) {
            throw new Exception('The config file does not exist');
        }

        $config = require $path;
        // Add JSON logic here in the future
        if (!is_array($config)) {
            throw new Exception('The config file do not return a array');
        }
        return $config;
    }
}