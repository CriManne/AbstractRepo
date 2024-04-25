<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T3')]
class T3 implements IModel{
    public function __construct(
        #[PrimaryKey(false)]
        #[Searchable]
        public string $id,
        #[Searchable]
        public string $v1
    ){}
}