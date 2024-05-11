<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\FindByQuery\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T2')]
class T2 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public int $id,
        #[Searchable]
        public string $v1,
        #[ManyToOne(columnName: 't1_id')]
        #[Searchable]
        public T1     $t1
    )
    {
    }
}