<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;

class EntityResetTest extends TestCase
{
    /**
     * Test reset entire entity
     */
    public function testResetEntity()
    {
        // Check setting data
        $entity = new Types([
            'integer' => 1337,
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
        ]);

        // Check that the entity contains the correct data
        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);

        // Reset the entity
        $entity->reset();

        // Now check that it contains the default values
        $this->assertEquals(0,     $entity->integer);
        $this->assertEquals(false, $entity->boolean);
        $this->assertEquals([],    $entity->array);
        $this->assertEquals('',    $entity->string);
        $this->assertEquals(null,  $entity->any);
    }


    /**
     * Test reset a single property
     */
    public function testResetSingleProperty()
    {
        // Check setting data
        $entity = new Types([
            'integer' => 1337,
            'boolean' => true,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => new StdClass,
        ]);

        // Check that the entity contains the correct data
        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);

        // Reset the entity
        $entity->resetProperty('string');

        // Check that only the above property got resetted
        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('',             $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);
    }
}
