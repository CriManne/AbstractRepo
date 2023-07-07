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
    #[Key(false)]
    public int $id;
    #[Required]
    public string $v1;
    #[ForeignKey(Relationship::MANY_TO_ONE)]
    public T1 $t1;

    function __construct(int $id,string $v1,T1 $t1){
        $this->id = $id;
        $this->v1 = $v1;
        $this->t1 = $t1;
    }
}