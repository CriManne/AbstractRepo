<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('ModelRecursiveForeignKey')]
class ModelRecursiveForeignKey implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public string $id,
        #[ForeignKey(Relationship::MANY_TO_ONE, 'test')]
        #[Searchable]
        public InvalidModelNoInterface $val
    ){}

}