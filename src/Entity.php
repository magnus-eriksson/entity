<?php namespace Maer\Entity;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use Traversable;

abstract class Entity implements JsonSerializable, ArrayAccess
{
    /**
     * Parameter list
     * @var array
     */
    protected $_params   = [];

    /**
     * Ignore these params when exporting the entity
     * @var array
     */
    protected $_protect  = [];

    /**
     * Specific entity settings
     * @var array
     */
    protected $_settings = [
        'silent' => false,
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
        $params = $this->__validate($params);

        // If we got a transformer, call it
        if ($transformer instanceof Closure) {
            $params = $this->__transform($transformer, $params);
        }

        // Get all defaults, data types and set the passed params
        foreach ($this->_params as $key => $value) {
            $this->_params[$key] = [
                'value'   => $value,
                'default' => $value,
                'type'    => strtolower(gettype($value)),
            ];
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
                $this->_params[$key]['value'] = $param['default'];
            }

            return;
        }

        foreach ($this->_params as $key => $param) {
            $this->_params[$key]['value'] = $param['default'];
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
    public function toArray($protect = null)
    {
        $protect = is_array($protect)
            ? $protect
            : $this->_protect;

        $params = $this->_params;

        if ($protect) {
            $params = $this->_params;

            foreach ($protect as $key) {
                unset($params[$key]);
            }
        }

        foreach ($params as $key => $p) {
            $params[$key] = $p['value'];
        }

        // Do json encode and decode to convert all levels to arrays
        return json_decode(json_encode($params), true, 512);
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

        return $this->_params[$key]['value'];
    }


    /**
     * Convert the value and store it
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (!$this->has($key)) {
            if (!$this->_silent) {
                throw new \Exception('The entity ' . __CLASS__ . ' do not have a parameter called ' . $key);
            }

            return;
        }

        // Since 'null' is a wildecard (any type goes), we don't want to cast those
        if ($this->_params[$key]['type'] != "null") {
            settype($value, $this->_params[$key]['type']);
        }

        return $this->_params[$key]['value'] = $this->__transform($key, $value);
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

        if (!is_array($params)) {
            if ($this->arrayGet($this->_setup, 'suppress_errors') === true) {
                return;
            }

            throw new InvalidArgumentException('Only arrays or objects implementing the Traversable interface allowed');
        }

        return $params;
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

    public function offsetSet($key, $value)
    {
        if (!$key) {
            throw new InvalidArgumentException("You can't push values to an entity");
        }

        if ($key && !$this->has($key)) {
            throw new InvalidArgumentException("Unknown property {$key}");
        }

        $this->__set($key, $value);
    }

    public function offsetExists($key)
    {
        return isset($this->_params[$key]);
    }

    public function offsetUnset($key)
    {
        throw new InvalidArgumentException("You can't unset an entity property");
    }

    public function offsetGet($key)
    {
        return $this->__get($key);
    }
}
