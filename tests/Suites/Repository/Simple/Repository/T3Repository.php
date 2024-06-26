<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T3;

class T3Repository extends AbstractRepository
{
    static public function getModel(): string
    {
        return T3::class;
    }
}