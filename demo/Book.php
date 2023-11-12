<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Required;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class Book implements IModel
{
    function __construct(
        #[Key(false)]
        public int    $id,
        #[Required]
        public string $val
    )
    {
    }
}