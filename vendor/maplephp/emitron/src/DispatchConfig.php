<?php

/**
 * Unit — Part of the MaplePHP Unitary Kernel/Dispatcher,
 * Configure the kernels dispatched behavior
 *
 * @package:    MaplePHP\Unitary
 * @author:     Daniel Ronkainen
 * @licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
 *              Don't delete this comment, it's part of the license.
 */

declare(strict_types=1);

namespace MaplePHP\Emitron;

use Exception;
use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Unitary\Config\ConfigProps;
use MaplePHP\Unitary\Interfaces\RouterDispatchInterface;
use MaplePHP\Unitary\Interfaces\RouterInterface;

class DispatchConfig implements DispatchConfigInterface
{
    private string $dir;
    private ?RouterInterface $router = null;
    protected ConfigPropsInterface $props;

    /**
     * @param string|ConfigPropsInterface|null $props
     * @throws Exception
     */
    public function __construct(string|null|ConfigPropsInterface $props = null)
    {
        if (!($props instanceof ConfigPropsInterface)) {
            $this->loadConfigFile(($props === null) ? __DIR__ . '/../emitron.config' : $props);
        }
    }

    /**
     * Get instance of ConfigProps
     *
     * @return ConfigPropsInterface|null
     */
    public function getProps(): ?ConfigPropsInterface
    {
        return $this->props;
    }

    /**
     * Set prop in ConfigProps
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setProp(string $key, mixed $value): self
    {
        $inst = clone $this;
        $inst->props = $this->props->setProp($key, $value);
        return $inst;
    }

    /**
     * Set multiple props in config
     *
     * @param array $props
     * @return $this
     */
    public function setProps(array $props): self
    {
        $inst = clone $this;
        $inst->props = $this->props->setProps($props);
        return $this;
    }

    /**
     * Get current exit code as int or null if not set
     *
     * @return RouterDispatchInterface
     */
    public function getRouter(): RouterDispatchInterface
    {
        if ($this->router === null) {
            return new class () implements RouterDispatchInterface {
                public function dispatch(callable $call): bool
                {
                    $call(['handler' => []], [], [], '');
                    return true;
                }
            };
        }
        return $this->router;
    }

    /**
     * Add exit after execution of the app has been completed
     *
     * @param callable $call
     * @return $this
     * @throws Exception
     */
    public function setRouter(callable $call): self
    {
        $inst = clone $this;
        $inst->router = $call($this->dir);
        if (!($inst->router instanceof RouterInterface)) {
            throw new Exception('Router must implement RouterInterface and "return" a it!');
        }
        return $inst;
    }

    /**
     * This method will be used top load the config file and is init in the constructor
     *
     * @param string $path
     * @return void
     * @throws Exception
     */
    public function loadConfigFile(string $path): void
    {
        $path = realpath($path);

        if ($path === false) {
            throw new Exception('The config file does not exist');
        }

        $config = require $path;
        // Add json logic here in the future
        if (!is_array($config)) {
            throw new Exception('The config file do not return a array');
        }

        //$this->dir = realpath(dirname($path));
        $this->dir = AbstractKernel::getRouterFilePath();
        $this->props = new ConfigProps($config);
    }
}
