<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Models\InvalidModelType2;

class InvalidModelTypeRepository2 extends AbstractRepository
{
    static public function getModel(): string
    {
        return InvalidModelType2::class;
    }
}