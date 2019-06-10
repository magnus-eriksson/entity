<?php namespace Maer\Entity;

use Closure;

class Helpers
{
    /**
     * Make one or multiple entities
     *
     * @param  string            $entity      Fully qualified entity name
     * @param  array|Traversable $data        Data to be populated
     * @param  string|null       $index       Which property should be used as index
     * @param  Closure|null      $modifier    Modify the data before population
     * @param  bool              $returnArray Return as array instead of a Collection instance
     *
     * @throws InvalidArgumentException if invalid
     * @return Entity|array|null
     */
    public static function makeEntities(string $entity, $data, string $index = null, ?Closure $modifier = null, bool $returnArray = false)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException(
                "Entity::make() requires an array or an object implementing the Traversable interface"
            );
        }

        // Check if we got a single set of data or a multidimensional array
        $multi = static::isMultiDimentional($data);

        if (!$multi) {
            return new $entity($data, $modifier);
        }

        $entities = $returnArray ? [] : new Collection;
        $counter  = 0;

        foreach ($data as $item) {
            if ($index && (!array_key_exists($index, $item) || is_null($item[$index]))) {
                throw new \InvalidArgumentException(
                    "The key '{$index}' is set as index but is missing from the data"
                );
            }

            $entities[$index ? $item[$index] : $counter++] = new $entity($item, $modifier);
        }

        return $entities;
    }


    /**
     * Check if a value can be casted as a string
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public static function canBeStringified($value): bool
    {
        return $value === null
            || is_scalar($value)
            || (is_object($value) && method_exists($value, '__toString'));
    }


    /**
     * Get the first entity in the collection
     *
     * @return Entity|null
     */
    public static function getFirstElement(array &$array) : ?Entity
    {
        if (count($array) === 0) {
            return null;
        }

        reset($array);

        return $array[key($array)];
    }


    /**
     * Get a list of values from a specific property.
     *
     * @param  array  $array
     * @param  string $property
     * @param  string $index    Set the property to use as index. Leave empty for an indexed array
     *
     * @return array
     */
    public function getPropertyValues(array &$array, string $property, string $index = null) : array
    {
        if (!$array) {
            // We got no entities
            return [];
        }

        $list = [];
        $idx  = 0;
        foreach ($array as $idx => $entity) {
            $list[$index ? $entity->{$index} : $idx++] = $entity->{$property};
        }

        return $list;
    }


    /**
     * Get the last entity in the collection
     *
     * @return Entity|null
     */
    public static function getLastElement(array &$array) : ?Entity
    {
        $count = count($array);

        if ($count === 0) {
            return null;
        }

        if ($count === 1) {
            return static::getFirstElement($array);
        }

        $items = array_slice($array, -1);

        return array_pop($items);
    }


    /**
     * Check if an array is multi dimensional
     *
     * @param  array   &$array
     *
     * @return boolean
     */
    public static function isMultiDimentional(array &$array) : bool
    {
        foreach ($array as $key => &$val) {
            if (!is_array($val)) {
                // We found an item that's not an array so it can't
                // be multidimensional
                return false;
            }
        }

        return true;
    }
}
