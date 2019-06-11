# Create predefined entity objects

> This version contains breaking changes and aren't compatible with version 1.x

[![Build Status](https://api.travis-ci.org/magnus-eriksson/entity.svg)](https://travis-ci.org/magnus-eriksson/entity)

Instead of passing around entities as arrays or instances of StdClass, where you never can trust if a specific key or property exists, or that they contain the correct datatype, it's better to use predefined entity objects.

I created this class when I was building a REST-api where I had data from different sources. MySQL, for example, defaults to returning all values as strings.

There are other situations where one parameter from the data source might have changed name, or been removed. Then it's nice if you don't need to go through all your code and change it where ever it's being used.


## Install

Clone this repository or use composer to download the library with the following command:
```bash
$ composer require maer/entity
```

## Usage

- [**Define an entity**](#define-an-entity)
    - [Default property values and types](#default-property-values-and-types)
    - [Protect properties](#protect-properties)
    - [Remap properties](#remap-properties)
    - [Remap nested properties](#remap-nested-properties)
- [**Instantiate an entity**](#instantiate-an-entity)
    - [Create a default entity](#create-a-default-entity)
    - [Convert an array to entity](#convert-an-array-to-entity)
    - [Convert a multidimensional array to a list of entities](#convert-a-multidimensional-array-to-a-list-of-entities)
    - [Get an array instead of collection](#get-an-array-instead-of-collection)
    - [Modify values on instantiation](#modify-values-on-instantiation)
    - [Change the behavior with the $\_setup property](#change-the-behavior-with-the-$_setup-property)
- [**Helper methods**](#helper-methods)
    - [Check if a property exists](#check-if-a-property-exists)
    - [Convert an entity to arrays](#convert-an-entity-to-arrays)
    - [Convert an entity to JSON](#convert-an-entity-to-json)
    - [Replace entity data](#replace-entity-data)
    - [Reset an entity](#reset-an-entity)
    - [Reset a property](#reset-a-property)
    - [Create your own helpers](#create-your-own-helpers)
- [**Collections**](#collections)
    - [Count](#count)
    - [Get the first entity](#get-the-first-entity)
    - [Get the last entity](#get-the-last-entity)
    - [Get a list of property values](#get-a-list-of-property-values)
    - [Sort the entities](#sort-the-entities)
    - [Remove an entity](#remove-an-entity)
- [**Add-ons**](#add-ons)
    - [DateTimeTrait](#datetimetrait)
        - [date()](#date)
        - [dateTime()](#datetime)
        - [timestamp()](#timestamp)
    - [TextTrait](#texttrait)
        - [excerpt()](#excerpt)
- [**Changes in version 2**](#changes-in-version-2)


## Define an entity

When defining an entity, you start create a new class which extends `Maer\Entity\Entity`:

```php
class Hero extends Maer\Entity\Entity {}
```

### Default property values and types

Creating an empty entity isn't that exiting. We should define some properties and default values:

```php
class Hero extends Maer\Entity\Entity
{
    protected $id        = 0;
    protected $name      = '';
    protected $awesome   = false;
    protected $someFloat = 0.0;
    protected $anything  = null;
}
```

When you define a default value, it's important that you set the default values to the correct data type. Later on when you're setting/updating a property value, the new value will be cast to the same data type as the default. The data types that are supported for automatic casting are: `integers`, `strings`, `booleans` and `floats/doubles`. If the default value is `null`, it can be set to anything.


### Protect properties

If you want to be able to use a property in your code, but don't want to expose it in, say, an API response, you can "protect" it. An example would be a user entity having a password hash. To protect (remove) a property on JSON serialization or when fetching it as an array, you can protect using the `protect()`-method like this:

```php
class User extends Maer\Entity\Entity
{
    protected $id           = 0;
    protected $name         = '';
    protected $passwordHash = '';

    protected function protect() : array
    {
        return [
            'passwordHash',
            // Keep adding property names to protect, if needed
        ];
    }
}
```

### Remap properties

Sometimes the source array might have different key names than the defined entity params. To make it as easy as possible for you, you can map param names and your entity will automatically remap them upon instantiation.

```php
class User extends Maer\Entity\Enity
{
    protected $username = '';

    protected function map() : array
    {
        // Assign the map as ['entityPropName' => 'sourcePropName']
        return [
            'username' => 'email',
        ];
    }
}
```
If you now send in an array with a `email` key, the value will be mapped as `username`.


### Remap nested properties

If you want to map a value in a multidimensional array, you can do just as above, but using dot notation as map key.

```php
class User extends Maer\Entity\Enity
{
    protected $username = '';

    protected function map() : array
    {
        return [
            'username' => 'user.username',
        ];
    }
}
```
This will map `['user' => ['username' => 'Chuck Norris']]` as just `username`. There is no limit on how many nested levels you can go.


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

Just remember that the values will be cast to the same datatype as the default value.

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

The static `Entity::make()` method is a bit more clever and can do more than just give you one entity. For example, if you pass a multidimensional array, it will give you a [Collection](#collections)-instance with entities back:

```php
$dataset = [
    [
        'id'   => 1337,
        'name' => 'Chuck Norris',
    ],
    [
        'id'   => 12345,
        'name' => 'Some guy',
    ],
];

$heroes = Hero::make($dataset);

echo $heroes[0]->id;
// Returns: 1337
```

You can also define what property should be used as the array key, making it an associative array.


```php
$heroes = Hero::make($dataset, 'id');

echo $heroes[1337]->name;
// Returns: "Chuck Norris"

```

### Get an array instead of collection

If you rather want the `make()`-method to return an array instead of a Collection-instance, you can pass `true` as the fourth argument:

```php
$heroes = Hero::make($dataset, null, null, true);
```

### Modify values on instantiation

Sometimes you get a list of values which needs to be modified before you create the entity. In this example, we will show an example of how we can prepend `http://` in front of an URL, in case it is missing:

Propose that we have an entity and dataset looking like this:

```php
class Website extends Maer\Entity\Entity
{
    protected $title = '';
    protected $url   = '';
}

$dataset = [
    'title'   => 'Google',
    'url'     => 'www.google.com',
];

$website = new Website($dataset);

echo $website->url;
// Returns: "www.google.com"
```

Sure, we could add `http://` before we instantiate the entity, but that would require us to repeat it where ever we instantiate the entity. It would also mean that we would need to manually iterate through the dataset, if it is a list of websites.

#### Modifier on instantiation

Luckily, we can send in a modifier in form of a closure upon entity creation:

```php
$website = new Website($dataset, function (array &$params)  {
    if (isset($params['url']) && strpos($params['url'], 'http://') !== 0) {
        // We got a parameter called url that doesn't start with http://
        $params['url'] = 'http://' . $params['url'];
    }
});
```

As you can see, the closure will get the `$params`-array as reference, meaning that it doesn't need to return anything.

You can do the same using the static `Entity::make()` method:
```php
$website = Website::make($dataset, null, function (array &$params) {
    // ... Modifie the values like the above example
});
```

#### Global modifier

Using a closure works well if you want to add a modifier for some specific instances. However, if you want to use your modifier for every instance, you can use the `modified()`-method instead:

```php
class Website extends Maer\Entity\Entity
{
    protected $title = '';
    protected $url   = '';


    protected function modifier(array $params)
    {
        if (isset($params['url']) && strpos($params['url'], 'http://') !== 0) {
            // We got a parameter called url that doesn't start with http://
            $params['url'] = 'http://' . $params['url'];
        }
    }
}

$dataset = [
    'title'   => 'Google',
    'url'     => 'www.google.com',
];

$website = new Website($dataset);
// Or
$website = Website::make($dataset);

echo $website->url;
// Returns: "http://www.google.com"
```

**Note:** If you have a global `modifier()`-method and still send in a modifier upon instantiation, the global modifier will be called first and your instance-specific modifier last.

## Helper methods

### Check if a property exists

If you try to get or set a property that doesn't exist, an `\InvalidArgumentException` will be thrown, except from when the entity is created through the `new Entity` or `Entity::make()`-method or when you use the `replace()`-method.

To check if a property exists, use the `has()`-method:

```php
if ($hero->has('name')) {
    // Yes, it exists and can be used
} else {
    // No, doesn't exist. Try to get/set it, an exception will be thrown
}
```


The first argument is the name of the property holding the value, the second argument is the format (defaults to: "F j, Y").

This method also works for properties holding unix timestamps.

### Convert an entity to arrays

If you need to convert an entity to array, use `asArray()`:

```php
$array = $hero->asArray();
// Return: ['id' => 1337, 'name' => 'Chuck Norris', ...]
```

_All properties returned by the `protect()`-method will be removed._

### Convert an entity to JSON

The `Entity`-class implements the `JsonSerializable`-interface, so just use `json_encode()`:

```php
$json = json_encode($hero);
// Return: "{"id": 1337, "name": "Chuck Norris", ...}"
```

_All properties returned by the `protect()`-method will be removed._


### Replace entity data
Sometimes you want to update the data in an entity. If it's just a few values, then doing `$entity->foo = 'bar';` will work fine, but if you have larger entities with a lot of properties you want to replace, you can use the `replace()`-method:

```php
// New data
$data = [
    'name' => 'Batman',
    ...
];

$entity->replace($data);

// $entity->name now has the value "batman"
```

This will replace the existing entity-properties with the data from the array.

You can also pass in a modifier:

```php
// New data
$data = [
    'name' => 'Batman',
    ...
];

$entity->replace($data, function (array $params) {
    // Modify the data
});

// $entity->name now has the value "batman"
```

### Reset an entity

If you, for what ever reason, need to reset an entity to it's default values, use the `reset()`-method:

```php
$entity->reset();
```

### Reset a property

If you just want to reset a specific property to it's default value, use the `resetProperty()`-method:

```php
$entity->resetProperty('nameOfTheProperty');
```

### Create your own helpers

You can of course create your own helpers:

```php
class Hero extends Maer\Entity\Entity
{
    protected $name = '';

    public function myNewHelper()
    {
        // Do stuff...
    }
}
```

And just access it like any other method: `$hero->myNewHelper()`

## Collections

When you use the `make()`-method to create multiple entities at the same time, you will get an instance of `Maer\Entity\Collection` back.

This class can be used as an array (implementing the ArrayAccess and Countable interfaces).

This class has some helper methods on it's own.

### Count

Get the collections entity count:

```php
echo $collection->count();
// same as count($collection)
```

### Get the first element

To get the first element in the collection, call the `first()`-method:

```php
$firstElement = $collection->first();

// $firstElement now contains the first entity in the collection
```

### Get the last element

To get the last element in the collection, call the `last()`-method:

```php
$firstElement = $collection->last();

// $firstElement now contains the last entity in the collection
```

### Get a list of property values

If you want to get all values (from all entities in a collection) from a single property:

```php
$names = $collection->list('username');

// Will give you something like:
// [
//     'firstUsername',
//     'secondUsername',
//     'thirdUsername',
//     ...
// ]
```

You can also define what property to use as index by passing the property name as a second argument:

```php
$names = $collection->list('username', 'id');

// Will give you something like:
// [
//     1337 => 'firstUsername',
//     1234 => 'secondUsername',
//     555  => thirdUsername',
//     ...
// ]
```

### Sort the entities

You can sort the entities in a collection by using the `usort()`-method:

```php
$collection->usort(function ($a, $b) {
    return $a->name <=> $b->name;
});
```

This method works exactly like the core function [usort()](https://www.php.net/manual/en/function.usort.php) (since it's using it in the background).

### Remove an entity

To remove an entity from the collection, use the `unset()`-method:

```php
$collection->unset('theEntityIndex');
```

## Add-ons

In version 1, you had the helper method `date()` in the base entity class. Since version 2, all helper methods are traits which you need to add to your entities yourself. This is to keep the entity classes as lean as possible.

### DateTimeTrait

The trait `Maer\Entity\Traits\DateTimeTrait` has some date-methods to make things a bit easier.

#### date

Get a property as a formatted date string:

```php
class Foo extends Maer\Entity\Entity
{
    protected $created = '2019-06-10 13:37:00';

    // Include the trait
    use Maer\Entity\Traits\DateTimeTrait;
}

$foo = new Foo();

// First argument is the property to use
echo $foo->date('created');
// Returns: June 10, 2019

// The second argument is the datetime format (standard PHP-date formats)
echo $foo->date('created', 'Y-m-d');
// Returns: 2019-06-10

// If you want to use a specific timezone, pass it as a third argument
echo $foo->date('created', 'Y-m-d', 'Europe/Stockholm');

```

#### dateTime

If you rather get a DateTime-instance back, use:

```php
// First argument is the propety to use
$date = $foo->dateTime('created');
// Returns an instance of DateTime

// If you want to use a specific timezone, pass it as a second argument
$date = $foo->dateTime('created', 'Europe/Stockholm');
```

#### timestamp

If you rather get the date as a timestamp, use:

```php
// First argument is the propety to use
$timestamp = $foo->timestamp('created');
// Returns: 1560166620

// If you want to use a specific timezone, pass it as a second argument
$timestamp = $foo->timestamp('created');
```

### TextTrait

The trait `Maer\Entity\Traits\TextTrait` has some text-methods to make things a bit easier.

#### excerpt

If you just want to display an excerpt of some text, you can use the `excerpt()`-method:

```php
class Foo extends Maer\Entity\Entity
{
    protected $content = 'Here be some lorem ipsum text';

    // Include the trait
    use Maer\Entity\Traits\TextTrait;
}

$foo = new Foo();

$maxLength = 20;
$suffix    = '...';

echo $foo->excerpt('content', $maxLength, $suffix);
// Returns: Here be some...
```

The length of the returned string can be less than the max length, but never more. The method makes sure no words are cropped and that the suffix fits within the max length.

The defaults are 300 for max length and "..." as suffix.


## Changes in version 2
Version 2 was completely rebuilt to use less memory and to be a bit faster. When tested creating 10.000 entities, it was just slightly faster, but the used memory was about 2-3 times less.

Quick overview of the biggest changes:

* Properties are now defined as protected class properties instead of `$_params = []`
* Entity::make() now returns an instance of Maer\Entity\Collection as default instead of an array
* Helper methods, like `$entity->date()`, has moved to traits and are no longer included in the base entity
* Settings, like map and protect, are now methods returning arrays instead of class properties


## Note

If you have any questions, suggestions or issues, let me know!

Happy coding!
