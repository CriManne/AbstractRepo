<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T2')]
class T2 implements IModel{
    public function __construct(
        #[PrimaryKey(false)]
        #[Searchable]
        public string $id,
        #[Searchable]
        public string $v1
    ){}
}