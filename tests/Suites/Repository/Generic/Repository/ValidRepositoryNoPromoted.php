<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelNoPromoted;

class ValidRepositoryNoPromoted extends AbstractRepository
{
    public static function getModel(): string
    {
        return ModelNoPromoted::class;
    }
}