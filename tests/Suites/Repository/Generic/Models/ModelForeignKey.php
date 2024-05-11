<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('ModelForeignKey')]
class ModelForeignKey implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public string $id,
        #[ManyToOne(columnName: 'test')]
        #[Searchable]
        public Model $val
    ){}

}