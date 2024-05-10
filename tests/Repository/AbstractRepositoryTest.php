<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\MockData\Models\T1;
use AbstractRepo\Test\MockData\Models\T2;
use AbstractRepo\Test\MockData\Models\T3;
use AbstractRepo\Test\MockData\Repository\TestInvalidModelRepository;
use ReflectionException;

class AbstractRepositoryTest extends BaseTest
{
    /**
     * @return void
     * @throws RepositoryException
     */
    public function testRelatedObjectNotFound(): void
    {
        self::expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);

        $t1 = new T1(1, "testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repo->save($t2);

        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        self::$t1Repo->delete($t1->id);
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        self::$t2Repo->findById(1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidRelationalModelSave(): void
    {
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(9999, "test");
        $t2 = new T2(2, "test2", $t1);
        self::$t2Repo->save($t2);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidRelationalModelDelete(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repo->save($t2);

        self::$t2Repo->delete(1);
        $this->assertNull(self::$t2Repo->findById(1));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInValidRelationalModelDelete(): void
    {
        $this->expectException(RepositoryException::class);
        $t1 = new T1(1, "test");
        $t2 = new T2(2, "test2", $t1);
        self::$t1Repo->save($t1);
        self::$t2Repo->save($t2);
        self::$t1Repo->delete(1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidRelationalModelUpdate(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repo->save($t2);

        $t2->v1 = "testUpdate";

        self::$t2Repo->update($t2);

        $this->assertEquals("testUpdate", self::$t2Repo->findById(1)->v1);
        $this->assertEquals("testRelation", self::$t2Repo->findById(1)->t1->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidRelationalModelUpdate(): void
    {
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(99, "test");
        $t2 = new T2(4, "test2", $t1);

        self::$t2Repo->update($t2);
    }
}