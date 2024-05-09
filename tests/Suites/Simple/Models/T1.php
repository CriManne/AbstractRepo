<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Simple\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T1')]
class T1 implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        public int     $id,
        #[Searchable]
        public string  $v1,
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}