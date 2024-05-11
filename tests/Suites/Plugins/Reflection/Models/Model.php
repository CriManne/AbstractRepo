<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Plugins\Reflection\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('Model')]
class Model implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        public int     $id,
        #[ManyToOne(columnName: 'test')]
        #[Searchable]
        public string  $v1,
        #[ManyToOne('test2')]
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}