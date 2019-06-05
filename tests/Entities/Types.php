<?php namespace Tests\Entities;

use Maer\Entity\Entity;

class Types extends Entity
{
    protected $integer = 0;
    protected $boolean = false;
    protected $array   = [];
    protected $string  = '';
    protected $any     = null;

    /**
     * Map properties
     *
     * @return array
     */
    protected function map() : array
    {
        return [
            'integer' => 'first.second.int',
        ];
    }
}
