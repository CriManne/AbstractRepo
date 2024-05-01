<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T3;
use AbstractRepo\Test\Models\T4;

class ForeignKeySearchTest extends BaseTest
{
    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelFkSearch(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repo->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repo->save($t4);

        $this->assertCount(1, self::$t4Repo->findByQuery('ABC'));
    }
}