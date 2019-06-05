<?php

use Maer\Entity\Registry;
use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;

class EntityDataTypeTest extends TestCase
{
    /**
     * Test correct data types
     */
    public function testCorrectDataTypes()
    {
        // Test with correct data types
        $entity = new Types([
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any!',
        ]);

        $this->assertInternalType('integer', $entity->integer);
        $this->assertInternalType('boolean', $entity->boolean);
        $this->assertInternalType('array',   $entity->array);
        $this->assertInternalType('string',  $entity->string);
        $this->assertInternalType('string',  $entity->any);
    }

    /**
     * Test incorrect data types
     */
    public function testIncorrectDataTypes()
    {
        // Test with correct data types
        $entity = new Types([
            'integer' => '1337',
            'boolean' => 1,
            'array'   => 'array',
            'string'  => null,
            'any'     => [],
        ]);

        // Test the data types
        $this->assertInternalType('integer', $entity->integer);
        $this->assertInternalType('boolean', $entity->boolean);
        $this->assertInternalType('array',   $entity->array);
        $this->assertInternalType('string',  $entity->string);
        $this->assertInternalType('array',   $entity->any);

        // Test the values
        $this->assertEquals(1337,      $entity->integer);
        $this->assertEquals(true,      $entity->boolean);
        $this->assertEquals(['array'], $entity->array);
        $this->assertEquals('',        $entity->string);
        $this->assertEquals([],        $entity->any);
    }

    /**
     * Test correct data types
     */
    public function testTypeConstants()
    {
        // Test with correct data types
        $entity = new Types();

        $this->assertEquals(Registry::TYPE_INT,  $entity->propertyType('integer'));
        $this->assertEquals(Registry::TYPE_BOOL, $entity->propertyType('boolean'));
        $this->assertEquals(Registry::TYPE_ARR,  $entity->propertyType('array'));
        $this->assertEquals(Registry::TYPE_STR,  $entity->propertyType('string'));
        $this->assertEquals(Registry::TYPE_ANY,  $entity->propertyType('any'));
    }

}
