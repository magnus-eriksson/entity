<?php namespace Maer\Entity;

use Exception;
use InvalidArgumentException;

class Registry
{
    /**
     * Type constants
     */
    const TYPE_BOOL    = 1;
    const TYPE_INTEGER = 2;
    const TYPE_FLOAT   = 3;
    const TYPE_STRING  = 4;
    const TYPE_ARRAY   = 5;
    const TYPE_ANY     = 6;

    /**
     * Type translation
     * @var array
     */
    protected $types = [
        'string'  => self::TYPE_BOOL,
        'integer' => self::TYPE_INTEGER,
        'double'  => self::TYPE_FLOAT,
        'string'  => self::TYPE_STRING,
        'array'   => self::TYPE_ARRAY,
        'any'     => self::TYPE_ANY,
    ];

    /**
     * Registry of entities and their property types & default values
     * @var array
     */
    protected $registry = [];


    /**
     * Register an entity
     *
     * @param  string $entityName
     * @param  array  $params
     *
     * @return $this
     */
    public function add(string $entityName, array $params = []): Registry
    {
        if ($this->has($entityName)) {
            // It's already registered. No need to do it again
            return $this;
        }

        $this->registry[$entityName] = [];

        // Check the default types
        foreach ($params as $key => $value) {
            $this->registry[$entityName][$key] = new Property(
                $this->getTypeIdFromString(gettype($value)),
                $value
            );
        }

        return $this;
    }


    /**
     * Check if an entity is registered
     *
     * @param  string  $entityName
     * @return boolean
     */
    public function has(string $entityName): bool
    {
        return array_key_exists($entityName, $this->registry);
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

        return $this->registry[$entityName][$propertyName]->type();
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

        if (!$this->has($entityName)) {
            throw new Exception(
                "The entity hasn't been registered in the registry"
            );
        }

        $type = $this->getPropertyType($entityName, $propertyName);

        switch ($type) {
            case self::TYPE_BOOL:
                return (bool)$value;
            case self::TYPE_INTEGER:
                return (int)$value;
            case self::TYPE_FLOAT:
                return (float)$value;
            case self::TYPE_STRING:
                if (!$this->canBeStringified($value)) {
                    throw new InvalidArgumentException(
                        "'{$entityName}->{$propertyName}': Type '" . gettype($value) . "' can not be cast as string"
                    );
                }
                return (string)$value;
            case self::TYPE_ARRAY:
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

        return array_map(function ($param) {
            return $param->value();
        }, $this->registry[$entityName]);
    }


    /**
     * Get the default value for a specific property
     *
     * @param  string $entityName
     * @param  string $propertyName
     * @return array
     */
    public function getDefaultValue(string $entityName, string $propertyName): array
    {
        $this->exceptionIfNotRegistered($entityName, $propertyName);

        return array_map(function ($param) {
            return $param->value();
        }, $this->registry[$entityName]);
    }


    /**
     * Check if a value can be casted as a string
     *
     * @param  mixed $value
     *
     * @return bool
     */
    protected function canBeStringified($value): bool
    {
        return $value === null
            || is_scalar($value)
            || (is_object($value) && method_exists($value, '__toString'));
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

        // Check the property name
        if (!$propertyName) {
            throw new InvalidArgumentException("Invalid property name: {$propertyName}");
        }

        // Check if the property exists
        if (!isset($this->registry[$entityName][$propertyName])) {
            throw new Exception("The entity {$entityName} doesn't have a property called '{$propertyName}'");
        }

        return true;
    }
}
