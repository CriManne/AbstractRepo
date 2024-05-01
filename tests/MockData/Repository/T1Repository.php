<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\MockData\Models\T1;

class T1Repository extends AbstractRepository
{
    static public function getModel(): string
    {
        return T1::class;
    }
}