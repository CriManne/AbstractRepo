<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('Order')]
class Order implements IModel{
    public function __construct(
        #[PrimaryKey(false)]
        public int $id,
        #[Searchable]
        public ?string $v1 = null,
    ){}
}