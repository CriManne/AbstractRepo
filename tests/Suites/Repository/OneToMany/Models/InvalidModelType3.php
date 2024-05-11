<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Attributes\OneToMany;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Interfaces\IModel;

#[Entity('InvalidModelType3')]
class InvalidModelType3 implements IModel
{
    public function __construct(
        #[PrimaryKey(autoIncrement: false)]
        public ?int   $id,
        #[Searchable]
        public string $v1,
        #[OneToMany(
            referencedField: 't0',
            referencedClass: InvalidModelReferencedClass::class
        )]
        public array  $manyT2
    )
    {
    }
}