<?php

class DotRevEntity extends Maer\Entity\Entity
{
    protected $_params = [
        'dotted'      => null,
        'some'        => 1337,
    ];

    protected $_map = [
        'dotted' => 'level1.level2',
        'some'   => 'hello.world',
    ];

    protected $_setup = [
        'invert_map' => true
    ];
}
