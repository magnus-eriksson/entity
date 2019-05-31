<?php namespace Maer\Entity;

use Closure;

class Helpers
{
    public static function makeEntities(string $entity, array $data, string $index = null, ?Closure $modifier = null)
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

        $entities = new Collection;
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
}
