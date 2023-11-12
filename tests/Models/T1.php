<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Required;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class T1 implements IModel{
    #[Key(false)]
    public int $id;
    #[Required]
    public string $v1;

    function __construct(int $id,string $v1){
        $this->id = $id;
        $this->v1 = $v1;
    }
}