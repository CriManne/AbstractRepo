<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('T5')]
class T5 implements IModel{
    public function __construct(
        #[PrimaryKey(false)]
        #[ForeignKey(relationship: Relationship::MANY_TO_ONE, columnName: 't4_id')]
        #[Searchable]
        public T4 $t3,
        #[Searchable]
        public string $v1
    ){}
}