<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelForeignKey;

class ValidRepositoryForeignKey extends AbstractRepository
{
    public static function getModel(): string
    {
        return ModelForeignKey::class;
    }
}