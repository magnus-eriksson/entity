<?php
use Maer\Entity\Entity;

/**
 * @coversDefaultClass \TestEntity
 */
class BeforeSetTest extends PHPUnit_Framework_TestCase
{
    protected $data = [
        'int'         => 1337,
    ];


    /**
    * @covers ::make
    */
    public function testClosure()
    {
        $entity = TestEntity::make($this->data, null, function ($params) {
            $params['int'] = 1234;
            return $params;
        });
        $this->assertEquals(1234, $entity->int, "Closure modifier");
    }


    /**
    * @covers ::__before
    */
    public function testBefore()
    {
        $entity = TestEntityBefore::make($this->data);
        $this->assertEquals(1234, $entity->int, "__before modifier");
    }

    /**
    * @covers ::__before & make closure
    */
    public function testBeforeClosurePriority()
    {
        $entity = TestEntityBefore::make($this->data, null, function ($params) {
            $params['int'] = 99;
            return $params;
        });
        $this->assertEquals(99, $entity->int, "__before modifier");
    }
}
