<?php namespace Maer\Entity;

use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Traversable;

abstract class Entity implements JsonSerializable
{
    /**
     * Parameter values
     *
     * @var array
     */
    protected $_params = [];

    /**
     * Removed on json serialization
     *
     * @var array
     */
    protected $_protect = [];

    /**
     * Map one key as another
     *
     * @var array
     */
    protected $_map = [];

    /**
     * @var boolean
     */
    protected $_ignoreExisting = false;

    /**
     * Setup
     *
     * @var array
     */
    protected $_setup = [];

    /**
     * Parameter data types
     *
     * @var array
     */
    protected $_types = [];

    /**
     * Create new instance
     *
     * @param array|object|Traversable $data
     * @param Closure|null             $modifier
     *
     * @throws UnknownPropertyException
     * @throws InvalidArgumentException if the passed value isn't an array or implements Traversable
     */
    public function __construct($data = [], Closure $modifier = null)
    {
        $this->setDefaultDataTypes();

        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (is_object($data)) {
            $data = (array)$data;
        }

        if (!is_array($data)) {
            if ($this->arrayGet($this->_setup, 'suppress_errors') === true) {
                return;
            }

            throw new InvalidArgumentException('Only arrays or objects implementing the Traversable interface allowed');
        }

        $convertSnakeToCamel = isset($this->_setup['snake_to_camel'])
            ? $this->_setup['snake_to_camel']
            : false;

        if ($convertSnakeToCamel === true) {
            $data = $this->convertSnakeToCamel($data);
        }

        $this->_ignoreExisting = true;

        // Run the before modifier
        $data = $this->__before($data);

        if (!is_null($modifier)) {
            $data = call_user_func_array($modifier, [$data]);
        }

        foreach ($this->_params as $key => $value) {
            if ($this->arrayHasKey($data, $key)) {
                $this->setParam($key, $this->arrayGet($data, $key));
                continue;
            }
        }

        // Overwrite with the mapped values
        $invertMap = $this->arrayGet($this->_setup, 'invert_map', false);
        foreach ($this->_map as $key1 => $key2) {
            if (!$invertMap && $this->arrayHasKey($data, $key1)) {
                $this->setParam($key2, $this->arrayGet($data, $key1));
                continue;
            }

            if ($invertMap && $this->arrayHasKey($data, $key2)) {
                $this->setParam($key1, $this->arrayGet($data, $key2));
                continue;
            }
        }

        $this->_ignoreExisting = false;
    }


    /**
     * Get a value from the parameter pool
     *
     * @param string $key
     *
     * @return mixed|null
     *
     * @throws UnknownPropertyException
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_params)) {
            if ($this->arrayGet($this->_setup, 'suppress_errors') === true) {
                return null;
            }

            throw new UnknownPropertyException("Unknown property: '{$key}'");
        }

        return $this->_params[$key];
    }


    /**
     * Alias for ::setParam()
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws UnknownPropertyException
     */
    public function __set($key, $value)
    {
        $this->setParam($key, $value);
    }


    /**
     * Set a value in the parameter pool and cast it to the same type as
     * the default value
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @throws UnknownPropertyException
     */
    protected function setParam($key, $value)
    {
        if (!array_key_exists($key, $this->_params)) {
            if ($this->_ignoreExisting || $this->arrayGet($this->_setup, 'suppress_errors') === true) {
                return;
            }

            throw new UnknownPropertyException("Unknown property: '{$key}'");
        }

        switch ($this->_types[$key]) {
            case "boolean":
                $this->_params[$key] = (bool)$value;
                break;
            case "integer":
                $this->_params[$key] = (integer)$value;
                break;
            case "float":
                $this->_params[$key] = (float)$value;
                break;
            case "string":
                $this->_params[$key] = (string)$value;
                break;
            case "array":
                $this->_params[$key] = (array)$value;
                break;
            default:
                $this->_params[$key] = $value;
                break;
        }
    }


    /**
     * Get the default data types
     */
    protected function setDefaultDataTypes()
    {
        foreach ($this->_params as $key => $value) {
            switch (gettype($value)) {
                case "boolean":
                    $this->_types[$key] = 'boolean';
                    break;
                case "integer":
                    $this->_types[$key] = 'integer';
                    break;
                case "double":
                    $this->_types[$key] = 'float';
                    break;
                case "string":
                    $this->_types[$key] = 'string';
                    break;
                case "array":
                    $this->_types[$key] = 'array';
                    break;
                default:
                    $this->_types[$key] = null;
                    break;
            }
        }
    }


    /**
     * Convert property names snake case to camel case
     * @param  array  $data
     * @return array
     */
    protected function convertSnakeToCamel(array $data)
    {
        foreach ($data as $key => $value) {
            $pos = strpos($key, '_');

            if ($pos === false) {
                continue;
            }

            // Create the new key
            $newKey = lcfirst(ucwords(trim(str_replace('_', ' ', $key))));
            $newKey = $pos === 0 ? '_' . $newKey : $newKey;
            $newKey = str_replace(' ', '', $newKey);

            // Add it to the data array
            $data[$newKey] = $value;

            // Unset the old key
            unset($data[$key]);
        }

        return $data;
    }


    /**
     * Check if a property is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_params[$key]);
    }


    /**
     * Check if a property exists
     *
     * This is used since property_exists doesn't work to
     * check if a property exists in the $this->_params array
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_params);
    }


    /**
     * Replace the data with the supplied array
     *
     * @param  array  $data
     */
    public function replace(array $data)
    {
        $convertSnakeToCamel = isset($this->_setup['snake_to_camel'])
            ? $this->_setup['snake_to_camel']
            : false;

        $data = $this->convertSnakeToCamel($data);

        foreach ($data as $key => $value) {
            if (!$this->has($key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }


    /**
     * Return the params as array and remove the protected keys
     *
     * @param array|null $protect
     *
     * @return array
     */
    public function toArray($protect = null)
    {
        $protect = is_array($protect)
            ? $protect
            : $this->_protect;

        if ($protect) {
            $new = $this->_params;


            foreach ($protect as $key) {
                unset($new[$key]);
            }

            // Do json encode and decode to convert all levels to arrays
            return json_decode(json_encode($new), true, 512);
        }

        // Do json encode and decode to convert all levels to arrays
        return json_decode(json_encode($this->_params), true, 512);
    }


    /**
     * Return the params as json and remove the protected keys
     *
     * @return array from json data
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


    /**
     * Return a parameter as a formatted date string
     *
     * @param  string $key
     * @param  string $format
     *
     * @return string
     */
    public function date($key, $format = "F j, Y")
    {
        if (is_numeric($this->_params[$key])) {
            return date($format, $this->_params[$key]);
        }

        return date($format, strtotime($this->_params[$key]));
    }


    /**
     * Modify the values before set
     *
     * @param  array $params
     *
     * @return array
     */
    protected function __before(array $params)
    {
        return $params;
    }


    /**
     * Convert array to entities
     *
     * @param  array   $data
     * @param  string  $index    Set the value in this key as index
     * @param  Closure $modifier Executed before the entity gets populated
     *
     * @return Entity|array
     */
    public static function make($data = null, $index = null, Closure $modifier = null)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (!is_array($data)) {
            return null;
        }

        if (count($data) < 1) {
            return [];
        }

        // Check if it is a multi dimensional array
        $values = array_values($data);
        $multi  = array_key_exists(0, $values) && is_array($values[0]);


        if ($multi) {
            $list = [];
            foreach ($data as $item) {
                if ($index && array_key_exists($index, $item)) {
                    $key        = $item[$index];
                    $list[$key] = static::populate($item, $modifier);
                    continue;
                }
                $list[] = static::populate($item, $modifier);
            }

            return $list;
        }

        return static::populate($data, $modifier);
    }


    /**
     * Populate the entity
     *
     * @param  array   $data
     * @param  Closure $modifier Executed before the entity get's populated
     *
     * @return Entity
     */
    protected static function populate(array $data, Closure $modifier = null)
    {
        return new static($data, $modifier);
    }


    /**
     * Get a key value, using dot notation
     *
     * @param  array  &$source
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function arrayGet(&$source, $key, $default = null)
    {
        if (!$key) {
            return $default;
        }

        if (array_key_exists($key, $source)) {
            return $source[$key];
        }

        $current =& $source;
        foreach (explode('.', $key) as $segment) {
            if (!array_key_exists($segment, $current)) {
                return $default;
            }
            $current =& $current[$segment];
        }

        return $current;
    }


    /**
     * Check if a key exists, using dot notation
     *
     * @param  array  &$source
     * @param  string $key
     *
     * @return boolean
     */
    protected function arrayHasKey(&$source, $key)
    {
        if (!$key || !is_array($source)) {
            return false;
        }

        if (array_key_exists($key, $source)) {
            return true;
        }

        $current =& $source;
        foreach (explode('.', $key) as $segment) {
            if (!array_key_exists($segment, $current)) {
                return false;
            }
            $current =& $current[$segment];
        }

        return true;
    }
}
