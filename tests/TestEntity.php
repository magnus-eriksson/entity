<?php

class TestEntity extends Maer\Entity\Entity
{
    protected $_params = [
        'int'         => 0,
        'int2'        => 0,
        'int3'        => 0,
        'double'      => 0.0,
        'double2'     => 0.0,
        'double3'     => 0.0,
        'bool'        => false,
        'bool2'       => false,
        'bool3'       => false,
        'string'      => null,
        'date'        => 0,
        'date2'       => null,
        'array_valye' => [],
        'protect'     => null,
        'pub'         => null,
        'null_value'  => null,
        'mapped'      => null,
    ];

    protected $_protect = [
        'protect'
    ];

    protected $_map = [
        'map' => 'mapped'
    ];
}
