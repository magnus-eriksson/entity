<?php namespace Maer\Entity;

use Closure;

class Helpers
{
    /**
     * Make one or multiple entities
     *
     * @param  string            $entity   Fully qualified entity name
     * @param  array|Traversable $data     Data to be populated
     * @param  string|null       $index    Which property should be used as index
     * @param  Closure|null      $modifier Modify the data before population
     *
     * @throws InvalidArgumentException if invalid
     * @return Entity|array|null
     */
    public static function makeEntities(string $entity, $data, string $index = null, ?Closure $modifier = null, bool $asArray = false)
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
        $multi = array_key_exists(0, $data) && is_array($data[0]);

        if (!$multi) {
            $data = $modifier ? $modifier($data) : $data;

            return new $entity($data);
        }

        $entities = $asArray ? [] : new Collection;
        $counter  = 0;

        foreach ($data as $item) {
            $item = $modifier ? $modifier($item) : $item;

            if ($index && (!array_key_exists($index, $item) || is_null($item[$index]))) {
                throw new \InvalidArgumentException(
                    "The key '{$index}' is set as index but is missing from the data"
                );
            }

            $entities[$index ? $item[$index] : $counter++] = new $entity($item);
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
}
