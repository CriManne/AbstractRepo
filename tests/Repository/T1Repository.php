<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;
use AbstractRepo\Interfaces\IRepository;
use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Models\T1;

class T1Repository extends AbstractRepository implements IRepository{
    static public function getModel():string{
        return T1::class;
    } 
}

?>