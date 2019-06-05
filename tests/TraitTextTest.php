<?php

use PHPUnit\Framework\TestCase;
use Tests\Entities\TextEntity;

class TraitTextTest extends TestCase
{
    /**
     * Test the excerpt()-method
     */
    public function testExcerptMethod()
    {
        $text   = '<b>Lorem ipsum dolor sit amet, consectetur adipiscing elit</b>';

        $entity = new TextEntity([
            'content' => $text,
        ]);

        // Check that the value were properly populated
        $this->assertEquals($text, $entity->content);

        // Check the max length
        $this->assertEquals('Lorem ipsum', $entity->excerpt('content', 15, ''));

        // Check the default suffix
        $this->assertEquals('Lorem ipsum...', $entity->excerpt('content', 15));

        // Check custom suffix
        $this->assertEquals('Lorem ipsum***', $entity->excerpt('content', 15, '***'));
    }
}
