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
     * Specific entity settings
     * @var array
     */
    protected $_settings = [
        'silent' => false,
    ];

    protected $_silent = false;


    /**
     * @param array $params
     */
    public function __construct(array $params = [], Closure $transformer = null)
    {
        $this->__init();

        if (!$params) {
            // Got no data, no need for the rest
            return;
        }

        // Call the transformer, if we got any
        if ($transformer) {
            $params = $transformer($params);
            if (!is_array($params)) {
                throw new \Exception('Transformers in the contructor must return an array');
            }
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
     * @param  array  $params
     */
    public function replace(array $params)
    {
        $this->reset();
        $this->update($params);
    }


    /**
     * Update the passed params
     *
     * @param  array  $params
     */
    public function update(array $params)
    {
        $this->_silent = true;

        foreach ($params as $key => $value) {
            $this->__set($key, $value);
        }

        $this->_silent = $this->_settings['silent'];
    }


    /**
     * Reset the entity by reverting all values to their defaults
     */
    public function reset()
    {
        foreach ($this->_params as $key => $param) {
            $this->_params[$key] = $param['default'];
        }
    }

    /**
     * Convert the value and store it
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->_params)) {
            if (!$this->_settings['silent']) {
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
     * Get a parameter
     *
     * @param string $key
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_params)) {
            if (!$this->_settings['silent']) {
                throw new \Exception('The entity ' . __CLASS__ . ' do not have a parameter called ' . $key);
            }

            return null;
        }

        return $this->_params[$key]['value'];
    }



    /**
     * Execute a transformer, if it's found
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function __transform($key, $value)
    {
        $method = '__transform' . ucfirst($key);
        if (!method_exists($this, $method)) {
            return $value;
        }

        return $this->{$method}($value);
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
}
