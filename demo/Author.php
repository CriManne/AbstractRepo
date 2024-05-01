<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class Author implements IModel
{
    function __construct(
        #[Key(false)]
        public int    $id,
        public string $val,
    )
    {
    }
}