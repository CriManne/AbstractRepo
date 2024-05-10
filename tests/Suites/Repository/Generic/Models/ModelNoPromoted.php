<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Models;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\PrimaryKey;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Interfaces\IModel;

#[Entity('Model')]
class ModelNoPromoted implements IModel
{
    #[PrimaryKey(autoIncrement: false)]
    public string $id;
    #[Searchable]
    public string $val;

    public function __construct(
        string $id,
        string $val
    ){
        $this->id = $id;
        $this->val = $val;
    }

}