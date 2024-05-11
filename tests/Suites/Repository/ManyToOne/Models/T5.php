<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\ManyToOne\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('T5')]
class T5 implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        #[ManyToOne(columnName: 't4_id')]
        #[Searchable]
        public T4     $t4,
        #[Searchable]
        public string $v1
    )
    {
    }
}