<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Plugins\Reflection\Models;

use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

class InvalidModelNoPk implements IModel
{
    public function __construct(
        public int     $id,
        #[Searchable]
        public string  $v1,
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}