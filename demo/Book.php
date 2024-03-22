<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class Book implements IModel
{
    function __construct(
        public string $val,
        #[ForeignKey(
            relationship: Relationship::MANY_TO_ONE,
            columnName: 'author_id'
        )]
        public Author $author,
        #[Key(true)]
        public ?int   $id = null
    )
    {
    }
}