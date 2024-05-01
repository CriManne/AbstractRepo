<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('Book')]
class Book implements IModel
{
    function __construct(
        public string $val,
        #[ForeignKey(
            relationship: Relationship::MANY_TO_ONE,
            columnName: 'author_id'
        )]
        public Author $author,
        #[PrimaryKey(true)]
        public ?int   $id = null
    )
    {
    }
}