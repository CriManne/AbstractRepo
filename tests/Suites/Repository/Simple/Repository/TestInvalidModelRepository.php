<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\MockData\Models\InvalidModel;

class TestInvalidModelRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return InvalidModel::class;
    }
}