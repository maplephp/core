<?php

namespace MaplePHP\Emitron\Contracts;

use Exception;
use MaplePHP\Unitary\Interfaces\RouterDispatchInterface;

interface DispatchConfigInterface
{

    /**
     * Get instance of ConfigProps
     *
     * @return ConfigPropsInterface|null
     */
    public function getProps(): ?ConfigPropsInterface;

    /**
     * Set prop in ConfigProps
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setProp(string $key, mixed $value): self;

    /**
     * Set multiple props in config
     *
     * @param array $props
     * @return $this
     */
    public function setProps(array $props): self;

    /**
     * Get current exit code as int or null if not set
     *
     * @return RouterDispatchInterface
     */
    public function getRouter(): RouterDispatchInterface;

    /**
     * Add exit after execution of the app has been completed
     *
     * @param callable $call
     * @return $this
     * @throws Exception
     */
    public function setRouter(callable $call): self;

    /**
     * This method will be used top load the config file and is init in the constructor
     *
     * @param string $path
     * @return void
     * @throws Exception
     */
    public function loadConfigFile(string $path): void;

}
