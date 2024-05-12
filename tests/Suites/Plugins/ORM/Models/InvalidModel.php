<?php

namespace AbstractRepo\Test\Suites\Plugins\ORM\Models;

use AbstractRepo\Interfaces\IModel;

class InvalidModel implements IModel
{
    private function __construct(
        public string $a,
        public string $b,
        public int    $c
    )
    {
    }
}