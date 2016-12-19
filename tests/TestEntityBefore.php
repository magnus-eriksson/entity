<?php

class TestEntityBefore extends TestEntity
{
    protected function __before(array $params)
    {
        $params['int'] = 1234;
        return $params;
    }
}
