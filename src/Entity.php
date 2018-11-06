<?php namespace Maer\Entity;

use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Traversable;

abstract class Entity implements JsonSerializable
{
    /**
     * Parameter list
     * @var array
     */
    protected $_params   = [];

    /**
     * Parameters meta data
     * @var array
     */
    protected $_meta   = [];

    /**
     * Ignore these params when exporting the entity
     * @var array
     */
    protected $_protect  = [];

    /**
     * User defined settings
     * @var array
     */
    protected $_settings = [];

    /**
     * Default entity settings
     * @var array
     */
    protected $_defaultSettings = [
        'silent'     => false,
    ];

    /**
     * Should we throw exceptions or should we keep quite?
     * @var boolean
     */
    protected $_silent = false;


    /**
     * @param array|object|Traversable $params
     */
    public function __construct(array $params = [], Closure $transformer = null)
    {
        $this->_settings = array_replace_recursive(
            $this->_defaultSettings,
            $this->_settings
        );

        // Get all parameters default value and types
        foreach ($this->_params as $key => $value) {
            $this->_meta[$key] = [
                'default' => $value,
                'type'    => strtolower(gettype($value)),
            ];
        }

        $params = $this->__validate($params);

        // If we got a transformer, call it
        if ($transformer instanceof Closure) {
            $params = $this->__transform($transformer, $params);
        }

        $this->update($params);
    }


    /**
     * Replace the complete entity with the passed params
     *
     * @param array|object|Traversable $params
     */
    public function replace($params)
    {
        $params = $this->__validate($params);
        $this->reset();
        $this->update($params);
    }


    /**
     * Update the passed params
     *
     * @param array|object|Traversable $params
     */
    public function update($params)
    {
        $params = $this->__validate($params);

        $this->_silent = true;

        foreach ($params as $key => $value) {
            $this->__set($key, $value);
        }

        $this->_silent = $this->_settings['silent'];
    }


    /**
     * Reset the entity (or specific key) by reverting all values to their defaults
     *
     * @param string $key If omitted, then the complete object will be reset
     */
    public function reset($key = null)
    {
        if (!$key && func_num_args() == 1) {
            // We got null as an argument, it might be an error on the user side.
            // let's just ignore it. To reset all, no argument should be passed at all
            return;
        }

        if ($key) {
            if ($this->has($key)) {
                $this->_params[$key] = $this->_meta[$key]['default'];
            }

            return;
        }

        foreach ($this->_meta as $key => $param) {
            $this->_params[$key] = $param['default'];
        }
    }


    /**
     * Check if a key exists
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_params);
    }


    /**
     * Return the entity as an array
     *
     * @param  array $protect
     * @return array
     */
    public function toArray(array $protect = [])
    {
        $protect = $protect ?: $this->_protect;
        $return  = $this->_params;

        if ($protect) {
            foreach ($protect as $key) {
                if (array_key_exists($key, $return)) {
                    unset($return[$key]);
                }
            }
        }

        // Do json encode and decode to convert all levels to arrays
        return json_decode(json_encode($return), true, 512);
    }


    /**
     * Get a parameter
     *
     * @param string $key
     */
    public function __get($key)
    {
        if (!$this->has($key)) {
            if (!$this->_silent) {
                throw new \Exception('The entity ' . __CLASS__ . ' do not have a parameter called ' . $key);
            }

            return;
        }

        return $this->_params[$key];
    }


    /**
     * Convert the value and store it
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (!$this->has($key) || !array_key_exists($key, $this->_meta)) {
            if (!$this->_silent) {
                throw new \Exception('The entity ' . __CLASS__ . ' do not have a parameter called ' . $key);
            }

            return;
        }

        // Since 'null' is a wildecard (any type goes), we don't want to cast those
        if ($this->_meta[$key]['type'] != "null") {
            settype($value, $this->_meta[$key]['type']);
        }

        return $this->_params[$key] = $this->__transform($key, $value);
    }


    /**
     * Convert array to entities
     *
     * @param  array   $params
     * @param  string  $index    Set the value in this key as index
     * @param  Closure $transformer Executed before the entity gets populated
     *
     * @return Entity|array
     */
    public static function make($params = null, $index = null, Closure $transformer = null)
    {
        $params = static::__validate($params);

        // Check if it is a multi dimensional array
        $multi = false;

        if ($params) {
            reset($params);
            $key   = key($params);
            $multi = is_int($key) && is_array($params[$key]);
        }

        if ($multi || (!$param && is_array($params))) {
            return new Collection($params, static::class, $index, $transformer);
        }

        return new static($params, $transformer);
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
     * Execute a transformer, if it's found
     *
     * @param  string|Closure $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function __transform($key, $value)
    {
        // Check if we got a closure as the first argument. If yes, then
        // we probably got this from the constuctor
        if ($key instanceof Closure) {
            $value = $key($value);

            if (!is_array($value)) {
                throw new \Exception('Transformers in the contructor must return an array');
            }

            return $value;
        }

        $method = '__transform' . ucfirst($key);
        if (!method_exists($this, $method)) {
            return $value;
        }

        return $this->{$method}($value);
    }


    /**
     * Check if the supplied value is traversable
     *
     * @param  array|object|Traversable $params
     * @throws \InvalidArgumentException if the param isn't traversable
     * @return array
     */
    protected function __validate($params)
    {
        if ($params instanceof Traversable) {
            $params = iterator_to_array($params);
        }

        if (is_object($params)) {
            $params = (array)$params;
        }

        if (!is_array($params) && !is_null($params)) {
            throw new InvalidArgumentException('Only arrays or objects implementing the Traversable interface allowed');
        }

        return $params;
    }
}
