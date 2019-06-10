<?php namespace Tests\Entities;

use Maer\Entity\Entity;

class TypesWithModifier extends Types
{
    protected function modifier(array &$params)
    {
        if (array_key_exists('integer', $params)) {
            $params['integer'] = $params['integer'] + 1000;
        }

        if (array_key_exists('string', $params)) {
            $params['string']  = 'mod_' . $params['string'];
        }

        if (array_key_exists('array', $params)) {
            $params['array']   = ['mod_' . $params['array'][0]];
        }

        if (array_key_exists('any', $params)) {
            $params['any']     = 'mod_' . $params['any'];
        }
    }
}
