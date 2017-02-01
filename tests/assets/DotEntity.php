<?php

class DotEntity extends Maer\Entity\Entity
{
    protected $_params = [
        'dotted'      => null,
    ];

    protected $_map = [
        'level1.level2' => 'dotted',
    ];
}
