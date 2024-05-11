<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\OneToMany;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('T1')]
class T1 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public ?int   $id,
        #[Searchable]
        public string $v1,
        #[OneToMany(
            referencedColumn: 't1_id',
            referencedClass: T2::class
        )]
        public ?array $manyT2 = null
    )
    {
    }
}