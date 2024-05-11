<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Repository\AbstractRepository;

class BookRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return Book::class;
    }
}

