<?php
use Maer\Entity\Entity;

/**
 * @coversDefaultClass \TestEntity
 */
class DotNotationTest extends PHPUnit_Framework_TestCase
{
    protected $data = [
        'level1'      => [
            'level2'    => 'foo'
        ],
    ];


    /**
    * @covers ::__construct
    */
    public function testMultiArray()
    {
        $entity = new DotEntity($this->data);
        $this->assertEquals('foo', $entity->dotted);
    }

    /**
    * @covers ::__construct
    */
    public function testMultiArrayRevMap()
    {
        $entity = new DotRevEntity($this->data);
        $this->assertEquals('foo', $entity->dotted);
    }

}
