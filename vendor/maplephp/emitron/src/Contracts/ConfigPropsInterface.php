<?php

namespace MaplePHP\Emitron\Contracts;

interface ConfigPropsInterface
{
    /**
     * Check if property exists
     *
     * @param string $key
     * @return bool|string
     */
    public function hasProp(string $key): bool|string;

    /**
     * Set config props - this is passed as configurations to the dispatcher
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setProp(string $key, mixed $value): self;

    /**
     * Set multiple config props
     *
     * @param array $props
     * @return self
     */
    public function setProps(array $props): self;

    /**
     * If you try to set unsupported properties, this will return them
     *
     * @return array
     */
    public function hasMissingProps(): array;

    /**
     * Get property value else null if not set nor exists
     *
     * @return array
     */
    public function get(string $key): mixed;

    /**
     * Return props object as array
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Get value as bool value
     *
     * @param string $value
     * @return bool
     */
    public function dataToBool(mixed $value): bool;
}