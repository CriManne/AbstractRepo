<?php

declare(strict_types=1);

namespace Demo\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Required;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class Book implements IModel
{
    #[Key(false)]
    public int $id;

    #[Required]
    public string $val;

    function __construct(int $id, string $val)
    {
        $this->id = $id;
        $this->val = $val;
    }
}