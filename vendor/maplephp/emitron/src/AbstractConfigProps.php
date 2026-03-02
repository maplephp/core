<?php

declare(strict_types=1);

namespace MaplePHP\Emitron;

use MaplePHP\DTO\Format\Str;
use MaplePHP\Emitron\Contracts\ConfigPropsInterface;

abstract class AbstractConfigProps implements ConfigPropsInterface
{
    /** @var array <int, string> */
    public array $missingProps = [];
    public ?string $test = null;

    /**
     * Hydrate the properties/object with expected data, and handle unexpected data
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    abstract protected function propsHydration(string $key, mixed $value): void;

    /**
     * Set type safe config props
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        if ($props !== []) {
            $this->setProps($props);
        }
    }

    /**
     * Check if property exists
     *
     * @param string|string $key
     * @return bool|string
     */
    public function hasProp(string $key): bool|string
    {
        if(str_contains($key, "-")) {
            $key = Str::value($key)->camelCaseFromSep()->get();
        }
        return property_exists($this, $key) ? $key : false;
    }

    /**
     * Set config props - this is passed as configurations to the dispatcher
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setProp(string $key, mixed $value): self
    {
        $newKey = $this->hasProp($key);
        if ($newKey === false) {
            $this->missingProps[] = $key;
        }
        $this->propsHydration($newKey, $value);
        return $this;
    }

    /**
     * Set multiple config props
     *
     * @param array $props
     * @return self
     */
    public function setProps(array $props): self
    {
        foreach ($props as $key => $value) {
            $this->setProp($key, $value);
        }
        return $this;
    }

    /**
     * If you try to set unsupported properties, this will return them
     *
     * @return array
     */
    public function hasMissingProps(): array
    {
        return $this->missingProps;
    }

    /**
     * Get property value else null if not set nor exists
     *
     * @return array
     */
    public function get(string $key): mixed
    {
        $newKey = $this->hasProp($key);
        return ($newKey !== false) ? $this->{$newKey} : null;
    }

    /**
     * Return props object as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get value as bool value
     *
     * @param string $value
     * @return bool
     */
    public function dataToBool(mixed $value): bool
    {
        return isset($value) && $value !== false && $value !== 'false';
    }

    /**
     * Quick validation of locale
     *
     * @param string $locale
     * @return bool
     */
    protected function isValidLocale(string $locale): bool
    {
        return (bool) preg_match('/^[a-z]{2,3}(?:_[A-Z]{2})?$/', $locale);
    }
}
