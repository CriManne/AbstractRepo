<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\Key;
use AbstractRepo\Attributes\Required;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity]
class T2 implements IModel{
    public function __construct(
        #[Key(false)]
        public int $id,
        public string $v1,
        #[ForeignKey(Relationship::MANY_TO_ONE)]
        public T1 $t1
    ){}
}