<?php

namespace AbstractRepo\Test\Suites\Plugins\ORM;

use AbstractRepo\Interfaces\IModel;

class Model implements IModel
{
    public function __construct(
        public string $a,
        public string $b,
        public int    $c
    )
    {
    }
}