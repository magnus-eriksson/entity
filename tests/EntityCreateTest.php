<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;
use Tests\Entities\TypesWithModifier;

class EntityCreateTest extends TestCase
{
    /**
     * Test population through the constructor
     */
    public function testConstructor()
    {
        // Check defaults
        $entity = new Types;

        $this->assertEquals(0,     $entity->integer);
        $this->assertEquals(false, $entity->boolean);
        $this->assertEquals([],    $entity->array);
        $this->assertEquals('',    $entity->string);
        $this->assertEquals(null,  $entity->any);

        // Check setting data
        $entity = new Types([
            'integer' => 1337,
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
        ]);

        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);
    }


    /**
     * Test population through the constructor with modifier method
     */
    public function testConstructorWithModifier()
    {
        // Check defaults
        $entity = new TypesWithModifier([
            'integer' => 1,
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any',
        ]);

        $this->assertEquals(1001,          $entity->integer);
        $this->assertEquals(true,         $entity->boolean);
        $this->assertEquals(['mod_hello'], $entity->array);
        $this->assertEquals('mod_foobar',  $entity->string);
        $this->assertEquals('mod_any',     $entity->any);
    }


    /**
     * Test population through the constructor with mapped properties
     */
    public function testConstructorWithMappedProperties()
    {
        // Check setting data
        $entity = new Types([
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
            'first'   => [
                'second' => [
                    'int' => 1337
                ],
            ],
        ]);

        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);
    }


    /**
     * Test population through Entity::make()
     */
    public function testMake()
    {
        // Create one
        $entity = Types::make([
            'integer' => 1337,
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
        ]);

        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);

        // Create many
        $entities = Types::make([
            [
                'integer' => 0,
                'boolean' => true,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'integer' => 1,
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1'
            ],
            [
                'integer' => 2,
                'boolean' => true,
                'array'   => ['hello2'],
                'string'  => 'foobar2',
                'any'     => 'any2'
            ],
        ]);

        $this->assertCount(3, $entities);

        $i = 0;
        foreach ($entities as $entity) {
            $this->assertEquals($i,             $entity->integer);
            $this->assertEquals(true,           $entity->boolean);
            $this->assertEquals(['hello' . $i], $entity->array);
            $this->assertEquals('foobar' . $i,  $entity->string);
            $this->assertEquals('any' . $i,     $entity->any);
            $i++;
        }
    }


    /**
     * Test population through Entity::make() and set custom index
     */
    public function testMakeWithCustomIndex()
    {
        // Create many
        $entities = Types::make([
            [
                'integer' => 0,
                'boolean' => true,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'integer' => 1,
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1'
            ],
            [
                'integer' => 2,
                'boolean' => true,
                'array'   => ['hello2'],
                'string'  => 'foobar2',
                'any'     => 'any2'
            ],
        ], 'string');

        $this->assertCount(3, $entities);

        $i = 0;
        foreach ($entities as $key => $entity) {
            $this->assertEquals($entity->string, $key);
            $this->assertEquals($i,              $entity->integer);
            $this->assertEquals(true,            $entity->boolean);
            $this->assertEquals(['hello' . $i],  $entity->array);
            $this->assertEquals('foobar' . $i,   $entity->string);
            $this->assertEquals('any' . $i,      $entity->any);
            $i++;
        }
    }


    /**
     * Test population through Entity::make() and set a modifier
     */
    public function testMakeWithModifier()
    {
        // Create the modifier
        $modifier = function (array &$params) {
            $params['integer'] = $params['integer'] + 1000;
            $params['string']  = 'mod_' . $params['string'];
            $params['array']   = ['mod_' . $params['array'][0]];
            $params['any']     = 'mod_' . $params['any'];
        };

        // Create many and add the modifier
        $entities = Types::make([
            [
                'integer' => 0,
                'boolean' => true,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'integer' => 1,
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1'
            ],
            [
                'integer' => 2,
                'boolean' => true,
                'array'   => ['hello2'],
                'string'  => 'foobar2',
                'any'     => 'any2'
            ],
        ], null, $modifier);

        $this->assertCount(3, $entities);

        $i = 0;
        foreach ($entities as $key => $entity) {
            $this->assertEquals($i + 1000,          $entity->integer);
            $this->assertEquals(true,               $entity->boolean);
            $this->assertEquals(['mod_hello' . $i], $entity->array);
            $this->assertEquals('mod_foobar' . $i,  $entity->string);
            $this->assertEquals('mod_any' . $i,     $entity->any);
            $i++;
        }
    }


    /**
     * Test population through Entity::make() with mapped properties
     */
    public function testMakeWithMappedProperties()
    {
        // Create one
        $entity = Types::make([
            'first' => [
                'second' => [
                    'int' => 1337,
                ],
            ],
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
        ]);

        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);

        // Create many
        $entities = Types::make([
            [
                'first' => [
                    'second' => [
                        'int' => 0,
                    ],
                ],
                'boolean' => true,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'first' => [
                    'second' => [
                        'int' => 1,
                    ],
                ],
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1'
            ],
            [
                'first' => [
                    'second' => [
                        'int' => 2,
                    ],
                ],
                'boolean' => true,
                'array'   => ['hello2'],
                'string'  => 'foobar2',
                'any'     => 'any2'
            ],
        ]);

        $this->assertCount(3, $entities);

        $i = 0;
        foreach ($entities as $entity) {
            $this->assertEquals($i,             $entity->integer);
            $this->assertEquals(true,           $entity->boolean);
            $this->assertEquals(['hello' . $i], $entity->array);
            $this->assertEquals('foobar' . $i,  $entity->string);
            $this->assertEquals('any' . $i,     $entity->any);
            $i++;
        }
    }
}
