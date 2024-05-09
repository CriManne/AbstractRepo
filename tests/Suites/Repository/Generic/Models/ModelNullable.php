<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('ModelNullable')]
class ModelNullable implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public ?int $id = null,
        #[Searchable]
        public ?string $val = null
    ){}

}