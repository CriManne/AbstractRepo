<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('T2')]
class T2 implements IModel{
    public function __construct(
        #[PrimaryKey(false)]
        public int $id,
        public string $v1,
        #[ForeignKey(
            relationship: Relationship::MANY_TO_ONE,
            columnName: 't1_id'
        )]
        public T1 $t1
    ){}
}