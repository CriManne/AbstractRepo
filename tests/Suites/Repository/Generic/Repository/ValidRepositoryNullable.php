<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelNullable;

class ValidRepositoryNullable extends AbstractRepository
{
    public static function getModel(): string
    {
        return ModelNullable::class;
    }
}