<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\FindByQuery\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Models\T2LeftJoin;

class T2LeftJoinRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return T2LeftJoin::class;
    }
}