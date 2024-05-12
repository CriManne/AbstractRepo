<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Interfaces\IModel;

#[Entity('T3')]
class T3 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public int   $id
    )
    {
    }
}