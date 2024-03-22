<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use AbstractRepo\Test\Models\T3;
use PHPUnit\Framework\TestCase;
use PDO;
use ReflectionException;
use AbstractRepo\Exceptions;

class AbstractRepositoryTest extends BaseTest
{
    /**
     * @return void
     * @throws RepositoryException
     * @throws ReflectionException
     */
    public function testInvalidModel(): void
    {
        $this->expectException(RepositoryException::class);

        new TestInvalidModelRepository(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelSaveAndFindById(): void
    {
        $t1 = new T1(1, "test");
        self::$t1Repo->save($t1);
        $this->assertEquals('test', self::$t1Repo->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelSaveAndFindByIdString(): void
    {
        $t3 = new T3("ABC", "testt3");
        self::$t3Repo->save($t3);
        $this->assertEquals('testt3', self::$t3Repo->findById("ABC")->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelSaveAndFindByIdWrongId(): void
    {
        $this->assertEquals(null, self::$t1Repo->findById(999));
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelSaveAndFindAll(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test");
            self::$t1Repo->save($t);
        }
        $this->assertCount(50, self::$t1Repo->findAll());
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelUpdateAndFindById(): void
    {
        $t1 = new T1(1, "test2");

        self::$t1Repo->save($t1);

        $t1->v1 = "test99";

        self::$t1Repo->update($t1);

        $this->assertEquals('test99', self::$t1Repo->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelUpdateAndFindByIdWrongId(): void
    {
        $t1 = new T1(1, "test99");

        self::$t1Repo->save($t1);

        $t1->id = 999;
        $t1->v1 = "test99";

        self::$t1Repo->update($t1);

        $this->assertEquals(null, self::$t1Repo->findById(999));
        $this->assertNotEquals(null, self::$t1Repo->findById(1));
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidModelDeleteAndFindById(): void
    {
        $t1 = new T1(2, "test2");
        self::$t1Repo->save($t1);
        $this->assertNotEquals(null, self::$t1Repo->findById($t1->id));
        self::$t1Repo->delete($t1->id);
        $this->assertEquals(null, self::$t1Repo->findById($t1->id));
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testValidRelationalModelSave(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repo->save($t2);

        $this->assertNotNull(self::$t2Repo->findById(1));
        $this->assertEquals("testRelation", self::$t2Repo->findById(1)->t1->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
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
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
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
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
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
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
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
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public function testInvalidRelationalModelUpdate(): void
    {
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(99, "test");
        $t2 = new T2(4, "test2", $t1);

        self::$t2Repo->update($t2);
    }
}