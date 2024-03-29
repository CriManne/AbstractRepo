<?php

declare(strict_types=1);

namespace Demo;

use AbstractRepo\Interfaces\IRepository;
use AbstractRepo\Repository\AbstractRepository;

class AuthorRepository extends AbstractRepository implements IRepository
{
    static public function getModel(): string
    {
        return Author::class;
    }
}

