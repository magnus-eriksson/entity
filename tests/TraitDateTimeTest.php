<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\DateEntity;

class TraitDateTimeTest extends TestCase
{
    /**
     * Test the dateTime()-method
     */
    public function testDatetimeMethod()
    {
        $date      = '2010-10-10 10:10:20';
        $timestamp = strtotime($date);

        $entity = new DateEntity([
            'date'      => $date,
            'timestamp' => $timestamp,
        ]);

        // Check that the values were properly populated
        $this->assertEquals($date,      $entity->date);
        $this->assertEquals($timestamp, $entity->timestamp);

        // Test getting a datetime-instance for the string date
        $dateTime   = $entity->dateTime('date');
        $dateTimeTs = $entity->dateTime('timestamp');

        // Check that we got an instance of DateTime
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertInstanceOf(DateTime::class, $dateTimeTs);

        // Check if the instance has the correct date
        $this->assertEquals($timestamp, $dateTime->getTimestamp());
        $this->assertEquals($timestamp, $dateTimeTs->getTimestamp());
    }


    /**
     * Test the date()-method
     */
    public function testDateMethod()
    {
        $date      = '2010-10-10 10:10:20';
        $timestamp = strtotime($date);

        $entity = new DateEntity([
            'date'      => $date,
            'timestamp' => $timestamp,
        ]);

        $this->assertEquals($date,      $entity->date);
        $this->assertEquals($timestamp, $entity->timestamp);

        // Test the Entity::date()-helper
        $expected = date('F j, Y', $timestamp);

        // Check the default format
        $this->assertEquals($expected, $entity->date('date'));
        $this->assertEquals($expected, $entity->date('timestamp'));

        // Check custom format
        $expected = date('Y-m-d', $timestamp);
        $this->assertEquals($expected, $entity->date('date', 'Y-m-d'));
        $this->assertEquals($expected, $entity->date('timestamp', 'Y-m-d'));
    }


    /**
     * Test the timestamp()-method
     */
    public function testTimestampMethod()
    {
        $date      = '2010-10-10 10:10:20';
        $timestamp = strtotime($date);

        $entity = new DateEntity([
            'date'      => $date,
            'timestamp' => $timestamp,
        ]);

        $this->assertEquals($date,      $entity->date);
        $this->assertEquals($timestamp, $entity->timestamp);

        $this->assertEquals($timestamp, $entity->timestamp('date'));
        $this->assertEquals($timestamp, $entity->timestamp('timestamp'));
    }
}
