<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Models\InvalidModelPrimaryKeyOnOneToMany;

class InvalidModelPrimaryKeyOnOneToManyRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return InvalidModelPrimaryKeyOnOneToMany::class;
    }
}