<?php
use Maer\Entity\Entity;

/**
 * @coversDefaultClass \TestEntity
 */
class SnakeToCamelTest extends PHPUnit_Framework_TestCase
{
    protected $data = [
        'camel_case' => 'foo',
        '_some_data' => 'bar',
    ];


    public function testSnakeToCamel()
    {
        $entity = new CamelCaseEntity($this->data);

        $this->assertEquals('foo', $entity->camelCase);
        $this->assertEquals('bar', $entity->_someData);
        $this->assertNull($entity->camel_case);

        // Test with the replace method
        $entity->replace([
            'camel_case' => 'test'
        ]);

        $this->assertEquals('test', $entity->camelCase);

        $entity->replace([
            'camelCase' => '1337'
        ]);

        $this->assertEquals('1337', $entity->camelCase);
    }
}
