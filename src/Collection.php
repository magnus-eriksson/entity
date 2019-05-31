<?php namespace Maer\Entity;

use ArrayAccess;
use Closure;
use Countable;
use JsonSerializable;
use InvalidArgumentException;

class Collection implements ArrayAccess, Countable, JsonSerializable
{
    /**
     * List of entities
     * @var array
     */
    protected $entities = [];

    /**
     * Collection type
     * @var string
     */
    protected $entity = null;


    /**
     * @param array $data
     * @param string $index Set the property to use as index. Leave empty for an indexed array
     */
    public function __construct(array $data = [], string $index = null)
    {
        if ($data) {
            $this->replace($data, $index);
        }
    }


    /**
     * Add an entity to the collection
     *
     * @param  int|string $offset
     * @param  Entity     $entity
     *
     * @throws InvalidArgumentException if the data is of wrong type
     */
    public function offsetSet($offset, $entity)
    {
        $this->exceptionOnInvalidType($entity);

        if (is_null($this->entities)) {
            $this->entities[] = $entity;
        } else {
            $this->entities[$offset] = $entity;
        }
    }

    /**
     * Replace all entities in the collection
     *
     * @param  array  $data
     * @param  string $index Set the property to use as index. Leave empty for an indexed array
     * @return $this
     */
    public function replace(array $data, string $index = null) : Collection
    {
        foreach ($data as $index => $item) {
            $this[$index] = $item;
        }

        return $this;
    }

    /**
     * Get the entity type for this collection
     *  - This will return null in case the collection is empty
     *
     * @return string
     */
    public function getType()
    {
        return $this->entity;
    }


    /**
     * Check if an index exists
     *
     * @param  string $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->entities[$offset]);
    }


    /**
     * Unset an entity
     *
     * @param  int|string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->entities[$offset]);
    }


    /**
     * Get an entity from the collection
     *
     * @param  int|string $offset
     *
     * @return Entity|null
     */
    public function offsetGet($offset) : ?Entity
    {
        return isset($this->entities[$offset]) ? $this->entities[$offset] : null;
    }


    /**
     * Get the current entity count
     *
     * @return int
     */
    public function count() : int
    {
        return count($this->entities);
    }


    /**
     * Return the collection as a real array
     *
     * @return array
     */
    public function asArray() : array
    {
        return $this->entities;
    }


    /**
     * Get the first entity in the collection
     *
     * @return Entity|null
     */
    public function first() : ?Entity
    {
        if ($this->count() === 0) {
            return null;
        }

        reset($this->entities);

        return $this->entities[key($this->entities)];
    }


    /**
     * Get the last entity in the collection
     *
     * @return Entity|null
     */
    public function last() : ?Entity
    {
        if ($this->count() === 0) {
            return null;
        }

        if ($this->count() === 1) {
            return $this->first();
        }

        $items = array_slice($this->entities, -1);

        return array_pop($items);
    }


    /**
     * Get a list of values from a specific property.
     *
     * @param  string $property
     * @param  string $index    Set the property to use as index. Leave empty for an indexed array
     *
     * @return array
     */
    public function list(string $property, string $index = null) : array
    {
        if (!$this->entities) {
            // We got no entities
            return [];
        }

        $list = [];
        $idx  = 0;
        foreach ($this->entities as $idx => $entity) {
            $list[$index ? $entity->{$index} : $idx++] = $entity->{$property};
        }

        return $list;
    }


    /**
     * Order the collection
     *
     * @param  string $property
     * @param  string $direction
     *
     * @param  $this
     */
    public function usort(Closure $sortCallback)
    {
        usort($this->entities, $sortCallback);

        return $this;
    }


    /**
     * Remove an entity fromt the collection
     *
     * @param  string $key
     */
    public function unset($key)
    {
        if (array_key_exists($key, $this->entities)) {
            unset($this->entities[$key]);
        }
    }


    /**
     * Check if a key is set
     *
     * @param  string  $key
     * @return boolean
     */
    public function __isset(string $key) : bool
    {
        return isset($this->entities[$key]);
    }

    /**
     * Return the collection as json
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        $entities = json_encode($this->entities);

        return json_decode($entities, true);
    }


    /**
     * Validate the entity type
     *
     * @param  mixed $entity
     *
     * @throws InvalidArgumentException if the data is of the wrong type
     */
    protected function exceptionOnInvalidType($entity)
    {
        if (!$entity instanceof Entity) {
            $entType = is_object($entity) ? get_class($entity) : gettype($entity);

            throw new InvalidArgumentException(
                'Expected an object extending ' . Entity::class. ' but got ' . $entType
            );
        }

        $type = $this->getType();

        if ($type && get_class($entity) !== $type) {
            throw new InvalidArgumentException(
                "Items in this collection must be of the type {$type}. Got " . get_class($entity)
            );
        }

        if (!$type) {
            $this->entity = get_class($entity);
        }
    }
}
