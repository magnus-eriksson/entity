<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;
use Tests\Entities\TypesWithModifier;

class EntityIssetTest extends TestCase
{
    /**
     * Test isset() on the entity
     */
    public function testIsset()
    {
        $data = [
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => null,
        ];

        $entity  = new Types($data);

        $this->assertTrue(isset($entity->integer));
        $this->assertTrue(isset($entity->boolean));
        $this->assertTrue(isset($entity->array));
        $this->assertTrue(isset($entity->string));
        $this->assertFalse(isset($entity->any));
        $this->assertFalse(isset($entity->nonExisting));
    }
}
