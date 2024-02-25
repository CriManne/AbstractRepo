<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class T3 implements IModel{
    public function __construct(
        #[Key(false)]
        public string $id,
        public string $v1
    ){}
}