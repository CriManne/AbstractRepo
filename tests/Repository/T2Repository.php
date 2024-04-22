<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Models\T2;

class T2Repository extends AbstractRepository
{
    static public function getModel():string{
        return T2::class;
    } 
}