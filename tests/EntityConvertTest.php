<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\Types;
use Tests\Entities\TypesWithProtect;

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


    /**
     * Test protect method
     */
    public function testProtectByMethod()
    {
        $data = [
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any!',
        ];

        $entity    = new Types($data);
        $protected = new TypesWithProtect($data);

        // Check the values
        $this->assertEquals(1337,      $entity->integer);
        $this->assertEquals(false,     $entity->boolean);
        $this->assertEquals(['hello'], $entity->array);
        $this->assertEquals('foobar',  $entity->string);
        $this->assertEquals('any!',    $entity->any);

        $this->assertEquals(1337,      $protected->integer);
        $this->assertEquals(false,     $protected->boolean);
        $this->assertEquals(['hello'], $protected->array);
        $this->assertEquals('foobar',  $protected->string);
        $this->assertEquals('any!',    $protected->any);

        $entArr  = $entity->asArray();
        $protArr = $protected->asArray();

        $this->assertCount(5, $entArr);
        $this->assertCount(3, $protArr);

        $this->assertArrayHasKey('integer', $entArr);
        $this->assertArrayHasKey('boolean', $entArr);
        $this->assertArrayHasKey('array',   $entArr);
        $this->assertArrayHasKey('string',  $entArr);
        $this->assertArrayHasKey('any',     $entArr);

        $this->assertArrayHasKey('integer', $protArr);
        $this->assertArrayHasKey('boolean', $protArr);
        $this->assertArrayHasKey('any',     $protArr);
        $this->assertArrayNotHasKey('array',   $protArr); // Protected
        $this->assertArrayNotHasKey('string',  $protArr); // Protected
    }


    /**
     * Test protect by argument
     */
    public function testProtectByArgument()
    {
        $data = [
            'integer' => 1337,
            'boolean' => false,
            'array'   => ['hello'],
            'string'  => 'foobar',
            'any'     => 'any!',
        ];

        $entity    = new Types($data);

        // Check the values
        $this->assertEquals(1337,      $entity->integer);
        $this->assertEquals(false,     $entity->boolean);
        $this->assertEquals(['hello'], $entity->array);
        $this->assertEquals('foobar',  $entity->string);
        $this->assertEquals('any!',    $entity->any);

        $entArr  = $entity->asArray();

        $this->assertCount(5, $entArr);
        $this->assertArrayHasKey('integer', $entArr);
        $this->assertArrayHasKey('boolean', $entArr);
        $this->assertArrayHasKey('array',   $entArr);
        $this->assertArrayHasKey('string',  $entArr);
        $this->assertArrayHasKey('any',     $entArr);

        $entArr  = $entity->asArray(['array', 'string']);

        $this->assertCount(3, $entArr);
        $this->assertArrayHasKey('integer', $entArr);
        $this->assertArrayHasKey('boolean', $entArr);
        $this->assertArrayNotHasKey('array',   $entArr);
        $this->assertArrayNotHasKey('string',  $entArr);
        $this->assertArrayHasKey('any',     $entArr);
    }
}
