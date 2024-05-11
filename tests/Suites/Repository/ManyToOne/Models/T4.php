<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\ManyToOne\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T4')]
class T4 implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        #[ManyToOne(columnName: 't3_id')]
        #[Searchable]
        public T3     $t3,
        #[Searchable]
        public string $v1
    )
    {
    }
}