<?php

class CamelCaseEntity extends Maer\Entity\Entity
{
    protected $_params = [
    	'camel_case'  => null, // Should be empty
        'camelCase'   => null,
        '_someData'   => null,
    ];

    protected $_setup = [
    	'snake_to_camel' => true,
    ];
}
