<?php namespace Maer\Entity;

use InvalidArgumentException;

class Entity
{
    /**
     * Entity properties
     *
     * @var array
     */
    protected $_props = [];

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

        // Register the entity
        static::$registry->add(static::class, $this->_props);

        $this->replace($params);
    }


    /**
     * Check if a value exits
     *
     * @param  string  $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->_props);
    }


    /**
     * Reset the entity to it's default values
     */
    public function reset()
    {
        $this->_props = static::$registry->getDefaultValues(static::class);
    }


    /**
     * Reset a property to it's default value
     *
     * @param  string $key
     */
    public function resetProp(string $key)
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException(static::class . " does not have any property called '{$key}'");
        }

        $this->_props[$key] = static::$registry->getPropDefaultValue(static::class, $key);
    }


    /**
     * Replace the existing data
     *
     * @param  array  $params
     *
     * @return $this
     */
    protected function replace(array $params = []): Entity
    {
        foreach ($params as $key => $value) {
            if ($this->has($key)) {
                $this->_props[$key] = static::$registry->castValue(static::class, $key, $value);
            }
        }

        return $this;
    }
}
