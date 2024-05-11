<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\OneToMany\Repository;

use AbstractRepo\Repository\AbstractRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Models\InvalidModelReferencedClass;

class InvalidModelReferencedClassRepository extends AbstractRepository
{
    static public function getModel(): string
    {
        return InvalidModelReferencedClass::class;
    }
}