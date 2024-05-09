<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Models\Model;

class InvalidRepositoryNoInterface extends AbstractRepository
{
    public static function getModel(): string
    {
        return InvalidRepositoryNoInterface::class;
    }
}