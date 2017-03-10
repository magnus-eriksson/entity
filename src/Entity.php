<?php namespace Maer\Entity;

use Closure;
use JsonSerializable;
use Traversable;

abstract class Entity implements JsonSerializable
{
    /**
     * Parameter values
     *
     * @var array
     */
    protected $_params  = [];

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
    protected $_map     = [];

    /**
     * @var boolean
     */
    protected $_ignoreExisting = false;

    /**
     * Setup
     * @var array
     */
    protected $_setup = [];


    /**
     * Create new instance
     *
     * @param array|Traversable     $data
     */
    public function __construct($data = [], Closure $modifier = null)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            throw new \Exception('Only arrays or objects implementing the Traversable interface allowed');
        }


        $this->_ignoreExisting = true;

        // Run the before modifier
        $data = $this->__before($data);

        if (!is_null($modifier)) {
            $data = call_user_func_array($modifier, [$data]);
        }

        foreach($this->_params as $key => $value) {
            $invertMap = $this->arrayGet($this->_setup, 'invert_map', false);
            $searchKey = $key;

            if (!$invertMap && $index = array_search($key, $this->_map)) {
                $key       = $this->_map[$index];
                $searchKey = $index;
            }

            if ($invertMap && array_key_exists($key, $this->_map)) {
                $searchKey = $this->_map[$key];
            }

            if ($this->arrayHasKey($data, $searchKey)) {
                $this->setParam($key, $this->arrayGet($data, $searchKey));
                continue;
            }
        }

        $this->_ignoreExisting = false;
    }


    /**
     * Get a value from the parameter pool
     *
     * @throws UnknownPropertyException
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_params)) {
            throw new UnknownPropertyException("Unknown property: '{$key}'");
        }

        return $this->_params[$key];
    }


    /**
     * Alias for ::setParam()
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
            if ($this->_ignoreExisting) {
                return;
            }

            throw new UnknownPropertyException("Unknown property: '{$key}'");
        }

        switch(gettype($this->_params[$key])) {

            case "boolean":
                $this->_params[$key] = (bool) $value;
                break;
            case "integer":
                $this->_params[$key] = (integer) $value;
                break;
            case "double":
                $this->_params[$key] = (float) $value;
                break;
            case "string":
                $this->_params[$key] = (string) $value;
                break;
            default:
                $this->_params[$key] = $value;
                break;
        }
    }


    /**
     * Check if a property is set.
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
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_params);
    }


    /**
     * Return the params as array and remove the protected keys
     *
     * @return json
     */
    public function toArray($protect = null)
    {
        $protect = is_array($protect)
            ? $protect
            : $this->_protect;

        if ($protect) {
            $new = $this->_params;

            foreach($protect as $key) {
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
     * @return json
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }


    /**
     * Return a parameter as a formatted date string
     *
     * @param  string   $key
     * @param  string   $format
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
     * @param  array  $params
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
     * @param  string  $index Set the value in this key as index
     * @param  Closure $modifier Executed before the entity gets populated
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
            foreach($data as $item) {
                if ($index && array_key_exists($index, $item)) {
                    $key = $item[$index];
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
     * @return Static
     */
    protected static function populate(array $data, Closure $modifier = null)
    {
        return new static($data, $modifier);
    }


    /**
     * Get a key value, using dot notation
     *
     * @param  array  &$array
     * @param  string $key
     * @param  mixed  $default
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

        $current  =& $source;
        foreach(explode('.', $key) as $segment) {
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
     * @param  array  &$array
     * @param  string $key
     * @return boolean
     */
    protected function arrayHasKey(&$source, $key)
    {
        if (!$key) {
            return false;
        }

        if (array_key_exists($key, $source)) {
            return true;
        }

        $current  =& $source;
        foreach(explode('.', $key) as $segment) {
            if (!array_key_exists($segment, $current)) {
                return false;
            }
            $current =& $current[$segment];
        }

        return true;
    }
}
