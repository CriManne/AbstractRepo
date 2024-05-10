<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelRecursiveForeignKey;

class ValidRepositoryRecursiveForeignKey extends AbstractRepository
{
    public static function getModel(): string
    {
        return ModelRecursiveForeignKey::class;
    }
}