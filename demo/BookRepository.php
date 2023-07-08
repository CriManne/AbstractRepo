<?php

declare(strict_types=1);

namespace Demo\Repository;

use AbstractRepo\Interfaces\IRepository;
use AbstractRepo\Repository\AbstractRepository;

class BookRepository extends AbstractRepository implements IRepository{


    static public function getModel():string{
        return Book::class;
    }

}

