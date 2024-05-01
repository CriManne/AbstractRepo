<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class Author implements IModel
{
    function __construct(
        #[PrimaryKey(false)]
        public int    $id,
        public string $val,
    )
    {
    }
}