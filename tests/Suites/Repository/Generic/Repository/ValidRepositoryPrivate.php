<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelPrivate;

class ValidRepositoryPrivate extends AbstractRepository
{
    public static function getModel(): string
    {
        return ModelPrivate::class;
    }
}