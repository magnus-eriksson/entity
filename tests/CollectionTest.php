<?php

use PHPUnit\Framework\TestCase;
use Maer\Entity\Collection;
use Tests\Entities\Types;
use Tests\Entities\TypesWithModifier;

class CollectionTest extends TestCase
{
    /**
     * Create a collection with entities
     *
     * @param  int    $count
     * @param  string $index Property value to use as index. Leave empty for numeric indexes
     *
     * @return \Tests\Entities\Collection
     */
    protected function createCollection(int $count = 3, string $index = null) : Collection
    {
        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'integer' => $i,
                'boolean' => true,
                'array'   => ['hello' . $i],
                'string'  => 'foobar' . $i,
                'any'     => 'any' . $i,
            ];
        }

        return Types::make($data, $index);
    }


    /**
     * Test to see that we get a collection
     */
    public function testCollection()
    {
        $entities = $this->createCollection(5);

        $this->assertInstanceOf(Collection::class, $entities);
        $this->assertCount(5, $entities);

        // Test to do a foreach
    }


    /**
     * Test first() and last()
     */
    public function testFirstAndLast()
    {
        $entities = $this->createCollection(5);

        $this->assertCount(5, $entities);

        // Test first()
        $first = $entities->first();

        $this->assertEquals(0,         $first->integer);
        $this->assertEquals('foobar0', $first->string);

        // Make sure we haven't changed the original data
        $this->assertCount(5, $entities);

        // Test last()
        $last = $entities->last();

        $this->assertEquals(4,         $last->integer);
        $this->assertEquals('foobar4', $last->string);

        // Make sure we haven't changed the original data
        $this->assertCount(5, $entities);
    }


    /**
     * Test type validation
     */
    public function testTypeValidation()
    {
        $entities = $this->createCollection(2);

        $this->assertCount(2, $entities);

        // Add an entity of same type
        $entities[] = new Types;

        $this->assertCount(3, $entities);

        // Add an entity of wrong type
        $this->expectException(InvalidArgumentException::class);
        $entities[] = new TypesWithModifier;
    }


    /**
     * Test fetching a list of values from a specific property
     */
    public function testList()
    {
        $entities = $this->createCollection(10);

        $strings  = $entities->list('string');

        // Check that the got what we expect
        $this->assertCount(10, $strings);

        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals('foobar' . $i, $strings[$i]);
        }

        // Get a list with 'any' as index
        $strings  = $entities->list('string', 'any');

        // Check that the got what we expect
        $this->assertCount(10, $strings);

        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals('foobar' . $i, $strings['any' . $i]);
        }
    }


    /**
     * Test sorting the arrays
     */
    public function testSort()
    {
        $entities = $this->createCollection(10);

        // Sort it on string descending order
        $entities->usort(function ($a, $b) {
            return strcmp($b->string, $a->string);
        });

        $first = $entities->first();
        $this->assertEquals('foobar9', $first->string);
    }


    /**
     * Test serialize as json
     */
    public function testJson()
    {
        $data = [
            [
                'integer' => 0,
                'boolean' => false,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'integer' => 1,
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1',
            ],
        ];

        $json        = json_encode($data);

        $collection  = Types::make($data);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);

        $collJson = json_encode($collection);

        $this->assertJson($collJson);
        $this->assertJsonStringEqualsJsonString($json, $collJson);

        // Test that we get the correct data back
        $fromJson = json_decode($collJson, true);

        $this->assertEquals($data, $fromJson);
    }


    /**
     * Test return array instead of Collection instance
     */
    public function testReturnArray()
    {
        $data = [
            [
                'integer' => 0,
                'boolean' => false,
                'array'   => ['hello0'],
                'string'  => 'foobar0',
                'any'     => 'any0',
            ],
            [
                'integer' => 1,
                'boolean' => true,
                'array'   => ['hello1'],
                'string'  => 'foobar1',
                'any'     => 'any1',
            ],
        ];

        // Fourth argument is $returnArray
        $array = Types::make($data, null, null, true);

        $this->assertInternalType('array', $array);
        $this->assertCount(2, $array);
    }
}
