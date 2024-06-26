<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\OneToMany;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('InvalidModelType2')]
class InvalidModelType2 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public ?int   $id,
        #[Searchable]
        public string $v1,
        #[OneToMany(
            referencedColumn: 't0',
            referencedClass: T1::class
        )]
        public array  $manyT2 = []
    )
    {
    }
}