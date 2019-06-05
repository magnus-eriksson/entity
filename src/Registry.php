<?php namespace Maer\Entity;

use Exception;
use InvalidArgumentException;

class Registry
{
    /**
     * Type constants
     */
    const TYPE_BOOL  = 1;
    const TYPE_INT   = 2;
    const TYPE_FLOAT = 3;
    const TYPE_STR   = 4;
    const TYPE_ARR   = 5;
    const TYPE_ANY   = 6;

    /**
     * Type translation
     * @var array
     */
    protected $types = [
        'boolean' => self::TYPE_BOOL,
        'integer' => self::TYPE_INT,
        'double'  => self::TYPE_FLOAT,
        'string'  => self::TYPE_STR,
        'array'   => self::TYPE_ARR,
        'any'     => self::TYPE_ANY,
    ];

    /**
     * Registry of entities and their property types & default values
     * @var array
     */
    protected $entities = [];


    /**
     * Register an entity
     *
     * @param  string $entityName
     * @param  array  $params
     * @param  string $privatePrefix
     * @param  array  $map
     * @param  array  $settings
     *
     * @return $this
     */
    public function add(string $entityName, array $params, string $privatePrefix = '__', array $map = [], array $settings = []): Registry
    {
        if ($this->has($entityName)) {
            // It's already registered. No need to do it again
            return $this;
        }

        $this->entities[$entityName] = [
            'defaults' => [],
            'types'    => [],
            'map'      => array_flip($map),
            'settings' => $settings,
        ];

        // Check the default types
        foreach ($params as $key => $value) {
            if (strpos($key, $privatePrefix) === 0) {
                continue;
            }

            $type = $this->getTypeIdFromString(gettype($value));

            $this->entities[$entityName]['defaults'][$key] = $value;
            $this->entities[$entityName]['types'][$key]    = $type;
        }

        return $this;
    }


    /**
     * Check if an entity is registered
     *
     * @param  string  $entityName
     * @return boolean
     */
    public function has(string $entityName, string $propertyName = null): bool
    {
        if (!array_key_exists($entityName, $this->entities)) {
            return false;
        }

        return is_null($propertyName)
            || array_key_exists($propertyName, $this->entities[$entityName]['defaults'])
            || array_key_exists($propertyName, $this->entities[$entityName]['map']);
    }


    /**
     * Get a setting value from an entity
     *
     * @param  string $entityName
     * @param  string $key
     * @param  mixed  $fallback
     *
     * @return mixed
     */
    public function getSetting(string $entityName, string $key, $fallback = null)
    {
        $this->exceptionIfNotRegistered($entityName);

        return $this->entities[$entityName]['settings'][$key] ?? $fallback;
    }


    /**
     * Create a new array with all mapped properties resolved
     *
     * @param  string $entityName
     * @param  array  $params
     *
     * @return array
     */
    public function resolveMappedProperties(string $entityName, array $params) : array
    {
        $this->exceptionIfNotRegistered($entityName);

        foreach ($this->entities[$entityName]['map'] as $key => $realKey) {
            if (!$this->arrayHas($params, $key)) {
                continue;
            }

            $params[$realKey] = $this->arrayGet($params, $key);
        }

        return $params;
    }


    /**
     * Check if a array key exists (using dot notation)
     *
     * @param  array  &$params
     * @param  string $key
     *
     * @return bool
     */
    protected function arrayHas(array &$params, string $key) : bool
    {
        $keys  = explode('.', $key);
        $value = $params;

        foreach ($keys as $k) {
            if (array_key_exists($k, $value)) {
                $value =& $value[$k];
                continue;
            }

            return false;
        }

        return true;
    }


    /**
     * Get a nested array value (using dot notation)
     *
     * @param  array  &$params
     * @param  string $key
     * @param  mixed  $fallback Returned if the key doesn't exist
     *
     * @return mixed
     */
    protected function arrayGet(array &$params, string $key, $fallback = null)
    {
        $keys  = explode('.', $key);
        $value = $params;

        foreach ($keys as $k) {
            if (array_key_exists($k, $value)) {
                $value =& $value[$k];
                continue;
            }

            return $falback;
        }

        return $value;
    }


    /**
     * Get a property's data type
     *
     * @param  string $entityName
     * @param  string $propertyName
     *
     * @throws \Exception if the entity or the propertyName doesn't exist
     *
     * @return integer
     */
    public function getPropertyType(string $entityName, string $propertyName): int
    {
        $this->exceptionIfNotRegistered($entityName, $propertyName);

        return $this->entities[$entityName]['types'][$propertyName];
    }


    /**
     * Cast a value
     *
     * @param  string $entityName
     * @param  string $propertyName
     * @param  mixed  $value
     *
     * @throws \Exception if the entity doesn't exist
     * @throws InvalidArgumentException if the value can't be casted correctly
     *
     * @return mixed
     */
    public function castValue(string $entityName, string $propertyName, $value)
    {
        $this->exceptionIfNotRegistered($entityName, $propertyName);

        $type = $this->getPropertyType($entityName, $propertyName);

        switch ($type) {
            case self::TYPE_BOOL:
                return (bool)$value;
            case self::TYPE_INT:
                return (int)$value;
            case self::TYPE_FLOAT:
                return (float)$value;
            case self::TYPE_STR:
                if (!Helpers::canBeStringified($value)) {
                    throw new InvalidArgumentException(
                        "'{$entityName}->{$propertyName}': Type '" . gettype($value) . "' can not be cast as string"
                    );
                }
                return (string)$value;
            case self::TYPE_ARR:
                return (array)$value;
            default:
                return $value;
        }
    }


    /**
     * Get type id from type string
     *
     * @param  string $type
     *
     * @return integer
     */
    public function getTypeIdFromString(string $type): int
    {
        return $this->types[$type] ?? $this->types['any'];
    }


    /**
     * Get the default values for an entity
     *
     * @param  string $entityName
     *
     * @return array
     */
    public function getDefaultValues(string $entityName): array
    {
        $this->exceptionIfNotRegistered($entityName);

        return $this->entities[$entityName]['defaults'];
    }


    /**
     * Get the default value for a specific property
     *
     * @param  string $entityName
     * @param  string $propertyName
     *
     * @return mixed
     */
    public function getPropertyDefaultValue(string $entityName, string $propertyName)
    {
        $this->exceptionIfNotRegistered($entityName, $propertyName);

        return $this->entities[$entityName]['defaults'][$propertyName];
    }


    /**
     * Get the property names of an entity
     *
     * @param  string $entityName
     *
     * @return array
     */
    public function getEntityProperties(string $entityName) : array
    {
        $this->exceptionIfNotRegistered($entityName);

        return array_keys($this->entities[$entityName]['defaults']);
    }


    /**
     * Check if an entity
     * @param  string       $entityName
     * @param  string|null  $propertyName
     *
     * @return bool
     */
    protected function exceptionIfNotRegistered(
        string $entityName,
        string $propertyName = null
    ): bool {
        // Check if we got a property name
        $checkProperty = func_num_args() == 2;

        // Check if the entity exists
        if (!$this->has($entityName)) {
            throw new Exception("The entity {$entityName} hasn't been properly registered");
        }

        // If we don't need to check the property, we're done
        if (!$checkProperty) {
            return true;
        }


        // Check if the property exists
        if (!$this->has($entityName, $propertyName)) {
            throw new Exception("The entity {$entityName} doesn't have a property called '{$propertyName}'");
        }

        return true;
    }
}
