<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;

#[Entity('Invalid')]
class InvalidModelNoInterface
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public string $id,
        #[Searchable]
        public string $val
    ){}

}