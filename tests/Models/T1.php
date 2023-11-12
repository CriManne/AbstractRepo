<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Required;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class T1 implements IModel{
    public function __construct(
        #[Key(false)]
        public int $id,
        public string $v1
    ){}
}