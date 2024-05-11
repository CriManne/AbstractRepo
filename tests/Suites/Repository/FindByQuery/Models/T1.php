<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\FindByQuery\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T1')]
class T1 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public ?int    $id,
        #[Searchable]
        public string  $v1,
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}