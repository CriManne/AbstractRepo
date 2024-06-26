<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity('T3')]
class T3 implements IModel{
    public function __construct(
        public ?string $v1 = null,
        #[PrimaryKey(true)]
        public ?int $id = null
    ){}
}