<?php namespace Tests\Entities;

use Maer\Entity\Entity;
use Maer\Entity\Traits\TextTrait;

class TextEntity extends Entity
{
    use TextTrait;

    protected $content = '';
}
