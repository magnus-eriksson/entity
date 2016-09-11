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

### Defining an entity

Create a class which extends the `\Maer\Entity\Entity` class and define the properties and default values.

```php

class Person extends \Maer\Entity\Entity
{
    // Create the entity parameters
    protected $_params = [
        'id'         => 0,
        'firstname'  => null,
        'surname'    => null,
        'is_nice'    => false,
        'created_at' => null,
    ];
}
```

When you set the default values, you also define the default data types. For example, `id` is set to `0` which means that every time you set that property, it will be cast as an integer: `$Person->id = "1";` will cast `"0"` to `0`.

If you define a property as `null`, it won't alter the value when it's set, so if you still need flexibility on any property, just set it as `null`.

The data types that will automatically be cast are:
* __Intergers__
* __Booleans__
* __Floats/Doubles__ _(will be cast as floats)_
* __Strings__


## Instantiate

### Single entity

There are two ways of instantiate a single entity.

The first way is by an ordinary class instantiation:

```php
// Get an entity object with default values
$person = new Person();

// Get an entity object and set some, or all, values
$person = new Person([
    'id'        => 1,
    'firstname' => 'Chuck',
    'surname'   => 'Norris',
]);
```

The second way is to use the built in `Entity::make()`-method:

```php
// Get an entity object with default values
$person = Person::make();

// Get an entity object and set some, or all, values
$person = Person::make([
    'id'        => 1,
    'firstname' => 'Chuck',
    'surname'   => 'Norris',
]);
```

These two ways looks kind of similar, so why have `make()`? The answer is in the next part...

### Multiple Entities

If you're instantiating the entities using `new Person();` and you have a collection (array) with data for several entities, like a database result, you would need to loop through the result and manually instantiate every entity. Since this just means that you need to write more code, you can let the `make()` method do this for you.

```php
// Get one entity object
$person = Person::make([
    'id'        => 1,
    'firstname' => 'Chuck',
    'surname'   => 'Norris',
]);

// Get an array with entity objects
$person = Person::make([
    [
        'id'        => 1,
        'firstname' => 'Chuck',
        'surname'   => 'Norris'
    ],
    [
        'id'        => 2,
        'firstname' => 'Lorem',
        'surname'   => 'Ipsum'
    ]
]);

```

...more documentation is coming soon...


## Note
If you have any questions, suggestions or issues, let me know!

Happy coding!

