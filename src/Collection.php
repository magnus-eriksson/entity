<?php namespace Maer\Entity;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var array
     */
    protected $_collection = [];


    /**
     * @param array        $data
     * @param string       $class
     * @param mixed        $index
     * @param Closure|null $transform [description]
     */
    public function __construct(array $data, $class, $index = null, Closure $transformer = null)
    {
        foreach ($data as $item) {
            if ($index && array_key_exists($index, $item)) {
                $this->_collection[$data[$index]] = new $class($item, $transformer);
            } else {
                $this->_collection[] = new $class($item, $transformer);
            }
        }
    }


    /**
     * Get the items as a PHP array
     *
     * @return array
     */
    public function items()
    {
        return $this->_collection;
    }


    /**
     * Return all items as arrays
     *
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->_collection), true, 512);
    }


    /**
     * Json serialize the collection
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->_collection;
    }


    /**
     * For the iterator aggregate inteface
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_collection);
    }


    /**
     * For the countable interface
     *
     * @return array
     */
    public function count()
    {
        return count($this->_collection);
    }


    /**
     * Set a collection item using array syntax
     *
     * @param  mixed $offset
     * @param  mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_collection[] = $value;
        } else {
            $this->_collection[$offset] = $value;
        }
    }


    /**
     * Check if an item exists, using array syntax
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_collection);
    }


    /**
     * Unset a collection item, using array syntax
     *
     * @param  mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_collection[$offset]);
    }


    /**
     * Get an item, using array syntax
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->_collection)
            ? $this->_collection[$offset]
            : null;
    }
}
