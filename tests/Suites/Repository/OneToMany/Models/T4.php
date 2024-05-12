<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\OneToMany;
use AbstractRepo\Attributes\OneToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T4')]
class T4 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        #[OneToOne('t3_id')]
        public T3   $t3,
        #[Searchable]
        public string $v1,
        #[OneToMany(
            referencedColumn: 't4_id',
            referencedClass: T5::class
        )]
        public ?array $manyT5 = null
    )
    {
    }
}