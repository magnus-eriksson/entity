# Create predefined entity objects

[![Build Status](https://api.travis-ci.org/magnus-eriksson/entity.svg)](https://travis-ci.org/magnus-eriksson/entity)

Instead of passing around entities as arrays or instances of StdClass, where you never can trust if a specific key or property exists, or that they contain the correct datatype, it's better to use predefined entity objects.

I created this class when I was building a REST-api where I had data from different sources. MySQL, for example, defaults to returning all values as strings.

There are other situations where one parameter from the data source might have changed name, or been removed. Then it's nice if you don't need to go through all your code and change it where ever it's being used.


## Install

Clone this repository or use composer to download the library with the following command:
```bash
$ composer require maer/entity 1.*
```

## Usage

- [**Define an entity**](#define-an-entity)
    - [Default property values and types](#default-property-values-and-types)
    - [Protect properties](#protect-properties)
- [**Instantiate an entity**](#instantiate-an-entity)
    - [Create a default entity](#create-a-default-entity)
    - [Convert an array to entity](#convert-an-array-to-entity)
    - [Convert a multidimensional array to a list of entities](#convert-a-multidimensional-array-to-a-list-of-entities)
- [**Helper methods**](#helper-methods)
    - [Check if a property exists](#check-if-a-property-exists)
    - [Get a property formatted as date](#get-a-property-formatted-as-date)
    - [Convert an entity to arrays](#convert-an-entity-to-arrays)
    - [Convert an entity to JSON](#convert-an-entity-to-json)
    - [Create your own helpers](#create-your-own-helpers)


## Define an entity

When defining an entity, you start create a new class which extends `Maer\Entity\Entity`:

```php
class Hero extends Maer\Entity\Enity {}
```

### Default property values and types

Creating an empty entity isn't that exiting. We should define some properties and default values:

```php
class Hero extends Maer\Entity\Enity
{
    protected $_params = [
        'id'        => 0,
        'name'      => '',
        'awesome'   => false,
        'someFloat' => 0.0,
        'anything'  => null,
    ];
}
```

When you define a default value, it's important that you define it with the correct data type. Later on when you're setting/updating a property value, the new value will be cast to the same data type as the default. The data types that are supported for automatic casting are: `integers`, `strings`, `booleans` and `floats/doubles`. If the default value is `null`, it can be set to anything.


### Protect properties

If you want to be able to use a property in your code, but don't want to expose it in, say, an API response, you can "protect" it. An example would be a user entity having a password hash. To protect (remove) a property on JSON serialization or when fetching it as an array, you can protect it like this:

```php
class User extends Maer\Entity\Enity
{
    protected $_params = [
        'id'           => 0,
        'name'         => '',
        'passwordHash' => '',
    ];

    protected $_protect = [
        'passwordHash',
        // add more if needed
    ];
}
```

## Instantiate an entity

You can instantiate an entity in several ways, depending on your needs.

### Create a default entity

Since it's a class, you can create a new entity with the default values like this:

```php
$hero = new Hero();

echo $hero->id;
// Returns: 0, just as we defined earlier.

```

You can also set new values for it by passing an array to the constructor:
```php
$hero = new Hero([
    'id' => 1337,
]);

echo $hero->id;
// Returns: 1337, just as we defined earlier.

```

Just remember that the values will be cast'ed to the same datatype as the default value.

### Convert an array to entity

When creating just one entity, you can use the above constructor method, or you can use the static `Entity::make()` method:
```php
$hero = Hero::make([
    'id' => 1337,
]);

echo $hero->id;
// Returns: 1337

```

### Convert a multidimensional array to a list of entities

The static `Entity::make()` method is a bit more clever and can do more than just give you one entity. If you pass a multidimensional array, it will give you an array with entities back:

```php
$dataSet = [
    [
        'id'   => 1337,
        'name' => 'Chuck Norris',
    ],
    [
        'id'   => 12345,
        'name' => 'Some guy',
    ],
];

$heroes = Hero::make($dataSet);

echo $heroes[0]->id;
// Returns: 1337
```

You can also define what property should be used as the array key, making it an associated array.

```php
$heroes = Hero::make($dataSet, 'id');

echo $heroes[1337]->name;
// Returns: "Chuck Norris"

```

## Helper methods

### Check if a property exists

If you try to get or set a property that doesn't exist, an `Maer\Entities\UnknowPropertyException` will be thrown, except from when the entity is created thru the `new Entity` or `Entity::make()`-method.

To check if a property exists, use the `has()`-method:

```php
if ($hero->has('name')) {
    // Yes, it exists and can be used
} else {
    // No, doesn't exist. Try to get/set it, an exception will be thrown
}
```

### Get a property formatted as date

If you have a date property, you can get it formatted by using the `date()`, method.


```php
$hero = Hero::make([
    'someDate' => '2016-09-14 23:35:00',
]);

echo $hero->date('someDate', "F j, Y");
// Return: "September 14, 2016"
```

The first argument is the name of the property holding the value, the second argument is the format (defaults to: "F j, Y").

This method also works for properties holding unix timestamps.

### Convert an entity to arrays

If you need to convert an entity to array, use `toArray()`:

```php
$array = $hero->toArray();
// Return: ['id' => 1337, 'name' => 'Chuck Norris', ...]
```

_All properties in the `$_protect = []` list will be removed._

### Convert an entity to JSON

The `Entity`-class implements the `JsonSerializable`-interface, so just use `json_encode()`:

```php
$json = json_encode($hero);
// Return: "{"id": 1337, "name": "Chuck Norris", ...}"
```

_All properties in the `$_protect = []` list will be removed._

### Create your own helpers

You can of course create your own helpers:

```php
class Hero extends Maer\Entity\Entity
{
    protected $_params = [
        // Some properties
    ];

    public function myNewHelper()
    {
        // Do stuff...
    }
}
```

And just access it like any other method: `$hero->myNewHelper()`

## Note
If you have any questions, suggestions or issues, let me know!

Happy coding!

