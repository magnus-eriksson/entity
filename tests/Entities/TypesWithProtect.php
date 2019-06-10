<?php namespace Tests\Entities;

use Maer\Entity\Entity;

class TypesWithProtect extends Types
{
    protected function protect() : array
    {
        return [
            'array',
            'string'
        ];
    }
}
