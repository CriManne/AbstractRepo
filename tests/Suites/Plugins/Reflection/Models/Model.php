<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Plugins\Reflection\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('Model')]
class Model implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        public int     $id,
        #[ForeignKey(Relationship::MANY_TO_ONE, 'test')]
        #[Searchable]
        public string  $v1,
        #[ForeignKey(Relationship::MANY_TO_ONE, 'test2')]
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}