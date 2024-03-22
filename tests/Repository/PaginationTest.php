<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use AbstractRepo\Test\Models\T3;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use AbstractRepo\Exceptions;

class PaginationTest extends BaseTest
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     */
    public function testValidSingleModelPagination(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals('test108', self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getData()[0]->v1);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     */
    public function testTotalPages(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals(10, self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getTotalPages());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     */
    public function testOverPagination(): void
    {
        $this->assertEmpty(self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getData());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     */
    public function testFirstPagePagination(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertCount(10, self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getData());
        $this->assertEquals('test100', self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getData()[0]->v1);
    }
}