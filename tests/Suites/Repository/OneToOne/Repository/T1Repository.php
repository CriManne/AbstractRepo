<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToOne\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\OneToOne\Models\T1;

class T1Repository extends AbstractRepository
{
    static public function getModel(): string
    {
        return T1::class;
    }
}