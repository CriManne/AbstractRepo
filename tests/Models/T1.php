<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;
use JetBrains\PhpStorm\Deprecated;

#[Entity]
class T1 implements IModel
{
    public function __construct(
        #[Key(false)]
        public int     $id,
        #[Searchable]
        public string  $v1,
        #[Searchable]
        public ?string $v2 = null
    )
    {
    }
}