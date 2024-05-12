<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity('T5')]
class T5 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: true)]
        public ?int   $id,
        #[ManyToOne('t4_id')]
        public T4 $t4
    )
    {
    }
}