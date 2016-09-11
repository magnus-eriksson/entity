<?php namespace Maer\Entity;

use JsonSerializable;

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
     * Create new instance
     *
     * @param array     $data
     */
    public function __construct(array $data = [])
    {
        $this->_ignoreExisting = true;
        foreach($data as $key => $value) {

            if (array_key_exists($key, $this->_map)) {
                $this->{$this->_map[$key]} = $value;
            } else {
                $this->{$key} = $value;
            }

        }
        $this->_ignoreExisting = false;
    }


    /**
     * Get a value from the parameter pool
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_params)) {
            throw new \Exception('Class ' . __CLASS__ . ' has no parameter called ' . $key);
        }

        return $this->_params[$key];
    }


    /**
     * Set a value in the parameter pool and cast it to the same type as
     * the default value
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->_params)) {
            if ($this->_ignoreExisting) {
                return;
            }

            throw new \Exception('Class ' . __CLASS__ . ' has no parameter called ' . $key);
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
            return $new;
        }

        return $this->_params;
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
     * Convert array to entities
     *
     * @param  array    $data
     * @return Entity|array
     */
    public static function make(array $data = [])
    {
        if (count($data) < 1) {
            return static::populate([]);
        }

        // Check if it is a multi dimensional array
        $values = array_values($data);
        $multi  = array_key_exists(0, $values) && is_array($values[0]);


        if ($multi) {
            $list = [];
            foreach($data as $item) {
                $list[] = static::populate($item);
            }
            return $list;
        }

        return static::populate($data);
    }


    /**
     * Populate the entity
     *
     * @param  array  $data
     * @return Static
     */
    protected static function populate(array $data)
    {
        return new static($data);
    }

}