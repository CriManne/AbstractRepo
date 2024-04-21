<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('T4')]
class T4 implements IModel{
    public function __construct(
        #[Key(false)]
        #[ForeignKey(relationship: Relationship::MANY_TO_ONE, columnName: 't3_id')]
        #[Searchable]
        public T3 $t3,
        #[Searchable]
        public string $v1
    ){}
}