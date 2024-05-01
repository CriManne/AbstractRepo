<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Interfaces\IRepository;
use AbstractRepo\Repository\AbstractRepository;
use Demo\Book;

class BookRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return Book::class;
    }
}

