<?php namespace Tests\Entities;

use Maer\Entity\Entity;
use Maer\Entity\Traits\DateTimeTrait;

class DateEntity extends Entity
{
    use DateTimeTrait;

    protected $date      = null;
    protected $timestamp = 0;
}
