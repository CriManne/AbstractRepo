<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Plugins\Reflection\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ManyToOne;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('Model')]
class ModelPrimaryKeyAndForeignKey implements IModel
{
    public function __construct(
        #[PrimaryKey(false)]
        #[ManyToOne(columnName: 'id_fk')]
        public int     $id
    )
    {
    }
}