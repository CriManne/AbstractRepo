<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('Model')]
class ModelPrivate implements IModel
{
    private function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public string $id,
        #[Searchable]
        public string $val
    ){}

}