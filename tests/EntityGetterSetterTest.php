<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;

class EntityGetterSetterTest extends TestCase
{
    /**
     * Test setting values using __set
     */
    public function testSetter()
    {
        // Check defaults
        $entity = new Types;

        $this->assertEquals(0,     $entity->integer);
        $this->assertEquals(false, $entity->boolean);
        $this->assertEquals([],    $entity->array);
        $this->assertEquals('',    $entity->string);
        $this->assertEquals(null,  $entity->any);

        // Set some data
        $entity->integer = 1337;
        $entity->boolean = true;
        $entity->array   = ['hello'];
        $entity->string  = 'foobar';
        $entity->any     = new StdClass;

        $this->assertEquals(1337,           $entity->integer);
        $this->assertEquals(true,           $entity->boolean);
        $this->assertEquals(['hello'],      $entity->array);
        $this->assertEquals('foobar',       $entity->string);
        $this->assertInstanceOf('StdClass', $entity->any);

        // NOTE: There's no need to test the __get-method since it's already
        // being indirect tested in most of the other tests. If it wouldn't work,
        // the other tests would already fail
    }
}
