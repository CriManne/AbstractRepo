<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\OneToMany;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('InvalidModelType3')]
class InvalidModelPrimaryKeyOnOneToMany implements IModel
{
    public function __construct(
        #[Searchable]
        public string $v1,
        #[PrimaryKey(autoIncrement: false)]
        #[OneToMany(
            referencedColumn: 't0',
            referencedClass: T1::class
        )]
        public ?array $manyT2 = null
    )
    {
    }
}