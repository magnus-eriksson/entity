<?php namespace Maer\Entity;

use Closure;
use InvalidArgumentException;
use JsonSerializable;
use OutOfBoundsException;

abstract class Entity implements JsonSerializable
{
    /**
     * @var \Maer\Entity\Registry
     */
    protected static $registry;


    /**
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        // Make sure we have an instance of the registry class
        if (is_null(static::$registry)) {
            static::$registry = new Registry;
        }

        if (!$this->has(static::class)) {
            // Register the entity to the registry
            $props = get_object_vars($this);
            static::$registry->add(
                static::class,
                $props,
                $this->ignorePrefix(),
                $this->map(),
                $this->settings()
            );
        }

        // Set the values
        $this->replace($params);
    }


    /**
     * Create one or more entities
     *
     * @param  array|Traversable $data
     * @param  string|null       $index Property value that should be used as index
     * @param  Closure           $modifier
     * @param  boolean           $asArray
     *
     * @throws InvalidArgumentException if getting invalid data
     *
     * @return Entity|[]
     */
    public static function make($data, ?string $index = null, Closure $modifier = null)
    {
        return Helpers::makeEntities(static::class, $data, $index, $modifier);
    }


    /**
     * Check if a value exits
     *
     * @param  string  $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return static::$registry->has(static::class, $key);
    }


    /**
     * Get the data type of a property
     *
     * @param  string  $key
     * @return int
     */
    public function propertyType(string $key): int
    {
        return static::$registry->getPropertyType(static::class, $key);
    }


    /**
     * Get a value
     *
     * @param  string $key
     *
     * @throws OutOfBoundsException if the property doesn't exist
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        $this->exceptionOnUnknownKey($key);

        return $this->{$key};
    }


    /**
     * Check if a property exists
     *
     * @param  string  $key
     * @return boolean
     */
    public function __isset(string $key)
    {
        return isset($this->{$key});
    }


    /**
     * Set a value
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @throws OutOfBoundsException if the property doesn't exist
     *
     * @return mixed  $value The value will be returned
     */
    public function __set(string $key, $value)
    {
        $this->exceptionOnUnknownKey($key);

        return $this->{$key} = static::$registry->castValue(static::class, $key, $value);
    }


    /**
     * Reset the entity to it's default values
     *
     * @return $this
     */
    public function reset(): Entity
    {
        $this->replace(static::$registry->getDefaultValues(static::class));

        return $this;
    }


    /**
     * Reset a property to it's default value
     *
     * @param  string $key
     *
     * @return $this
     */
    public function resetProperty(string $key): Entity
    {
        $this->__set($key, static::$registry->getPropertyDefaultValue(static::class, $key));

        return $this;
    }


    /**
     * Replace the existing data
     *
     * @param  array   $params
     *
     * @return $this
     */
    public function replace(array $params = []): Entity
    {
        // Resolve mapped properties
        $params = static::$registry->resolveMappedProperties(static::class, $params);

        // Set the values we got
        $params = $this->modifier($params);

        foreach ($params as $key => $value) {
            if (!$this->has($key)) {
                continue;
            }

            $this->__set($key, $value);
        }

        return $this;
    }


    /**
     * Get the entity as array
     *
     * @param  array  $ignore Keys to ignore (overrides protect())
     * @return array
     */
    public function asArray(array $ignore = []) : array
    {
        $properties = static::$registry->getEntityProperties(
            static::class
        );

        $data      = [];
        $protected = $ignore ?: $this->protect();

        foreach ($properties as $key) {
            if (in_array($key, $protected)) {
                continue;
            }

            $data[$key] = $this->{$key};
        }

        return $data;
    }


    /**
     * Return the data that should be serialized as json
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }


    /**
     * Get the prefix for ignored properties
     *
     * @return string
     */
    protected function ignorePrefix() : string
    {
        return '__';
    }


    /**
     * Get the list of properties to ignore when getting the entity
     * as an array or as json
     *
     * @return array
     */
    protected function protect() : array
    {
        return [];
    }


    /**
     * Map of array keys => property, using dot notation
     *
     * @return array
     */
    protected function map() : array
    {
        return [];
    }


    /**
     * Settings for this entity type
     *
     * @return array
     */
    public function settings() : array
    {
        return [];
    }


    /**
     * Get a setting value
     *
     * @param  string $key
     * @param  mixed  $fallback
     *
     * @return mixed
     */
    public function getSetting(string $key, $fallback = null)
    {
        return static::$registry->getSetting(static::class, $key, $fallback);
    }


    /**
     * Modify the params before populating the entity
     *
     * @param  array  $params
     * @return array
     */
    protected function modifier(array $params) : array
    {
        return $params;
    }


    /**
     * Check if the key exists, or throw an exception
     *
     * @param  mixed $key
     *
     * @throws OutOfBoundsException if the property doesn't exist
     */
    protected function exceptionOnUnknownKey($key)
    {
        if (!$this->has($key)) {
            throw new OutOfBoundsException(
                'The property ' . static::class . "->{$key} does not exist"
            );
        }
    }


    /**
     * Get a property name prefixed with the ignore prefix
     *
     * @param  string $name
     * @param  mixed  $fallback
     *
     * @return mixed
     */
    protected function getIgnoredProp(string $name, $fallback = null)
    {
        $prop = $this->ignorePrefix() . $name;

        return $this->{$prop} ?? $fallback;
    }
}
