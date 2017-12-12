<?php
use Maer\Entity\Entity;

/**
 * @coversDefaultClass \TypeEntity
 */
class TypeTest extends PHPUnit_Framework_TestCase
{
    /**
    * @covers ::__construct
    */
    public function testTypeChange()
    {
        $entity = new TestEntity([
            'int'         => '1234',
            'string'      => 'test',
        ]);

        $this->assertInternalType('integer', $entity->int);
        $this->assertInternalType('string',  $entity->string);

        $entity->int    = 'test';
        $entity->string = 1234;
        $this->assertInternalType('integer', $entity->int);
        $this->assertInternalType('integer', $entity->string);

        $entity->int    = 1234;
        $entity->string = 'hello';
        $this->assertInternalType('integer', $entity->int);
        $this->assertInternalType('string', $entity->string);
    }
}
