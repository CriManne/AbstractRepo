<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\FindByQuery\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T3')]
class T3 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public int $id,
        #[Searchable]
        public string $v1,
        #[ManyToOne(columnName: 't2_id')]
        #[Searchable]
        public T2     $t2
    )
    {
    }
}