<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\MockData\Models\T4;

class T4Repository extends AbstractRepository
{
    static public function getModel():string{
        return T4::class;
    } 
}