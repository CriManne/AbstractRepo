<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\ManyToOne\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity('T2')]
class T2 implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        public string $id,
        public string $v1,
        #[ManyToOne(columnName: 't1_id')]
        public T1     $t1
    )
    {
    }
}