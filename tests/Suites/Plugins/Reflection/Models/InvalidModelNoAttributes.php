<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Plugins\Reflection\Models;

use AbstractRepo\Interfaces\IModel;

class InvalidModelNoAttributes implements IModel
{
    public function __construct(
        public int     $id,
        public string  $v1,
        public ?string $v2 = null
    )
    {
    }
}