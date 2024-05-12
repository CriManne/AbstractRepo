<?php

namespace AbstractRepo\Test\Suites\Repository\Generic\Repository;

use AbstractRepo\Repository\AbstractRepository;

class InvalidRepositoryNoInterface extends AbstractRepository
{
    public static function getModel(): string
    {
        return InvalidRepositoryNoInterface::class;
    }
}