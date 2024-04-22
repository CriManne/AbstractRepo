<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Models\T4;

class T4Repository extends AbstractRepository
{
    static public function getModel():string{
        return T4::class;
    } 
}