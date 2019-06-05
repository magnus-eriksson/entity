<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;

class EntityConvertTest extends TestCase
{
    /**
     * Test serialize as json
     */
    public function testJson()
    {
        $data = [
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any!',
        ];

        $json = json_encode($data);

        $entity  = new Types($data);
        $entJson = json_encode($entity);

        $this->assertJson($entJson);
        $this->assertJsonStringEqualsJsonString($json, $entJson);

        // Test that we get the correct data back
        $fromJson = json_decode($entJson, true);

        $this->assertEquals($data, $fromJson);
    }


    /**
     * Test getting entity as an array
     */
    public function testArray()
    {
        $data = [
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any!',
        ];

        // Check defaults
        $entity  = new Types($data);
        $entArr  = $entity->asArray();

        $this->assertIsArray($entArr);
        $this->assertEquals($data, $entArr);
    }

}
