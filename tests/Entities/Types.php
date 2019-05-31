<?php namespace Tests\Entities;

use Maer\Entity\Entity;

class Types extends Entity
{
    /**
     * Entity properties
     * @var integer
     */
    protected $integer = 0;
    protected $boolean = false;
    protected $array   = [];
    protected $string  = '';
    protected $any     = null;
}