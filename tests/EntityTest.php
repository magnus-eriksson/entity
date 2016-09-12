<?php
require __DIR__ . '/TestEntity.php';

use Maer\Entity\Entity;

/**
 * @coversDefaultClass \TestEntity
 */
class EntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity;

    protected $time;

    protected $data = [
        'int'         => 1337,
        'int2'        => "1337",
        "int3"        => 13.37,
        'double'      => 13.37,
        'double2'     => "13.37",
        "double3"     => 1337,
        'bool'        => true,
        'bool2'       => "false",
        'string'      => "I'm a string",
        'date'        => 0,
        'date2'       => null,
        'array_value' => ['val0', 'val1', 'val2'],
        'protect'     => "I'm protected",
        'map'         => "success",
        'not_saved'   => "This won't be saved",
    ];


    public function __construct()
    {
        $this->time          = time();
        $this->data['date']  = $this->time;
        $this->data['date2'] = date('Y-m-d H:i:s', $this->time);

        $this->entity = TestEntity::make($this->data);
    }

    /**
    * @covers ::make
    */
    public function testInstantiate()
    {
        // Single instance
        $this->assertInstanceOf(
            'TestEntity',
            $this->entity,
            "Make single"
        );

        // Collection of instances
        $entities = TestEntity::make([$this->data, $this->data]);

        $this->assertTrue(
            is_array($entities),
            "Make multiple"
        );

        $this->assertContainsOnlyInstancesOf(
            'TestEntity',
            $entities,
            "Make mutliple"
        );

        // Collection with defined key
        $entities = TestEntity::make([$this->data, ['int' => 123]], 'int');

        $this->assertTrue(
            is_array($entities),
            "Make multiple custom key"
        );

        $this->assertContainsOnlyInstancesOf(
            'TestEntity',
            $entities,
            "Make mutliple custom key"
        );

        $this->assertCount(2, $entities, 'Multiple instances count');

        $this->assertArrayHasKey('1337', $entities, "Multiple has custom key");
        $this->assertArrayHasKey('123', $entities, "Multiple has custom key");
    }


    /**
    * Test data type integrity
    */
    public function testDataTypes()
    {
        // Integers
        foreach (['int', 'int2', 'int3'] as $key) {
            $this->assertInternalType(
                'integer',
                $this->entity->{$key},
                "Type integer: $key"
            );
        }

        // Floats
        foreach (['double', 'double2', 'double3'] as $key) {
            $this->assertInternalType(
                'float',
                $this->entity->{$key},
                "Type float: $key"
            );
        }

        // Booleans
        foreach (['bool', 'bool2'] as $key) {
            $this->assertInternalType(
                'boolean',
                $this->entity->{$key},
                "Type boolean: $key"
            );
        }

        $this->assertTrue($this->entity->bool, "Type bool value");
        $this->assertTrue($this->entity->bool2, "Type bool2 value");
    }


    /**
    * @convers ::__set
    */
    public function testSetters()
    {
        $ent = new TestEntity;

        // Integer
        $ent->int = "99";
        $this->assertInternalType(
            'integer',
            $ent->int,
            "int setter"
        );

        // Float
        $ent->double = "99.22";
        $this->assertInternalType(
            'float',
            $ent->double,
            "float setter"
        );

        // Boolean
        $ent->bool = "false";
        $this->assertInternalType(
            'boolean',
            $ent->bool,
            "bool setter"
        );
        $this->assertTrue($ent->bool);
    }


    /**
    * @convers ::toArray
    */
    public function testToArray()
    {
        $data = $this->entity->toArray();
        $this->assertInternalType(
            'array',
            $data,
            "toArray()"
        );
    }


    /**
    * @convers ::_protect
    */
    public function testProtected()
    {
        // Check default protected
        $data = $this->entity->toArray();

        $this->assertArrayNotHasKey(
            'protect',
            $data,
            "Check protected"
        );

        // Check method protected
        $data = $this->entity->toArray(['int']);

        $this->assertArrayHasKey(
            'protect',
            $data,
            "Check protected(['int']) : protect"
        );

        $this->assertArrayNotHasKey(
            'int',
            $data,
            "Check protected(['int']) : int"
        );
    }

    /**
    * @convers ::_map
    */
    public function testMap()
    {
        $this->assertEquals(
            'success',
            $this->entity->mapped,
            "Check mapped 'mapped'"
        );
    }


    /**
    * @convers ::jsonSerialize
    */
    public function testJsonSerialize()
    {
        $json = json_encode($this->entity);
        $this->assertJson(
            $json,
            "json_serialize entity"
        );
    }


    /**
    * @convers ::date
    */
    public function testDate()
    {
        $date = date('F j, Y', $this->time);
        $this->assertEquals(
            $date,
            $this->entity->date('date'),
            "Default date() from timestamp"
        );

        $this->assertEquals(
            $date,
            $this->entity->date('date2'),
            "Default date() from date string"
        );

        $this->assertEquals(
            date('Y-m-d H:i:s', $this->time),
            $this->entity->date('date', "Y-m-d H:i:s"),
            "Custom date() from timestamp"
        );

        $this->assertEquals(
            date('Y-m-d H:i:s', $this->time),
            $this->entity->date('date2', "Y-m-d H:i:s"),
            "Custom date() from date string"
        );
    }


    /**
    * @convers ::date
    */
    public function testIsset()
    {
        $this->assertTrue(
            isset($this->entity->int),
            "isset int"
        );

        $this->assertFalse(
            isset($this->entity->null_value),
            "isset null_value"
        );

        $this->assertFalse(
            isset($this->entity->invalid),
            "isset non existing property"
        );
    }


    /**
    * @convers ::has
    */
    public function testHas()
    {
        $this->assertTrue(
            $this->entity->has('int'),
            "has int"
        );

        $this->assertTrue(
            $this->entity->has('null_value'),
            "has null_value"
        );

        $this->assertFalse(
            $this->entity->has('invalid'),
            "has non existing property"
        );
    }
}
