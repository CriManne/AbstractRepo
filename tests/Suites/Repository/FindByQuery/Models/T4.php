<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\FindByQuery\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity('T4')]
class T4 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public int $id,
        public string $v1,
    )
    {
    }
}