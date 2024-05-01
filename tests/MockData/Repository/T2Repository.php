<?php

declare(strict_types=1);

namespace AbstractRepo\Test\MockData\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\MockData\Models\T2;

class T2Repository extends AbstractRepository
{
    static public function getModel():string{
        return T2::class;
    } 
}