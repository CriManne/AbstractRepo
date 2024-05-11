<?php

namespace AbstractRepo\Test\Suites\Repository\OneToMany;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\OneToMany\Models\T1;
use AbstractRepo\Test\Suites\Repository\OneToMany\Models\T2;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\InvalidModelPrimaryKeyOnOneToManyRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\InvalidModelReferencedClassRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\InvalidModelTypeRepository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\InvalidModelTypeRepository2;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\InvalidModelTypeRepository3;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\OneToMany\Repository\T2Repository;

class OneToManyTest extends BaseTestSuite
{
    public static T1Repository $t1Repository;
    public static T2Repository $t2Repository;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE T1;");
        self::$pdo->exec("TRUNCATE TABLE T2;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        self::$t1Repository = new T1Repository(self::$pdo);
        self::$t2Repository = new T2Repository(self::$pdo);
    }


    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelType(): void
    {
        $this->expectExceptionMessage(RepositoryException::ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE);

        new InvalidModelTypeRepository(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelType2(): void
    {
        $this->expectExceptionMessage(RepositoryException::ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE);

        new InvalidModelTypeRepository2(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelType3(): void
    {
        $this->expectExceptionMessage(RepositoryException::ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE);

        new InvalidModelTypeRepository3(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelPrimaryKeyOnOneToMany(): void
    {
        $this->expectExceptionMessage(RepositoryException::ONE_TO_MANY_CANNOT_BE_PRIMARY_KEY);

        new InvalidModelPrimaryKeyOnOneToManyRepository(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testModelSave(): void
    {
        $this->expectNotToPerformAssertions();

        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test", $t1);
        self::$t2Repository->save($t2);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testModelFindById(): void
    {
        $t1 = new T1(1, "testRelation");
        $t2 = new T2(1, "test", $t1);
        $t22 = new T2(2, "test2", $t1);

        self::$t1Repository->save($t1);
        self::$t2Repository->save($t2);
        self::$t2Repository->save($t22);

        $this->assertCount(2, self::$t1Repository->findById(1)->manyT2);
    }

//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testRelatedObjectNotFound(): void
//    {
//        self::expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
//
//        $t1 = new T1(1, "testRelation");
//        self::$t1Repository->save($t1);
//        $t2 = new T2(1, "test", $t1);
//        self::$t2Repository->save($t2);
//
//        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
//        self::$t1Repository->delete($t1->id);
//        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
//        self::$t2Repository->findById(1);
//    }
//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testInvalidModelSave(): void
//    {
//        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
//        $t1 = new T1(9999, "test");
//        $t2 = new T2(2, "test", $t1);
//        self::$t2Repository->save($t2);
//    }
//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testModelDelete(): void
//    {
//        $t1 = new T1(1, "testRelation");
//        self::$t1Repository->save($t1);
//        $t2 = new T2(1, "test2", $t1);
//        self::$t2Repository->save($t2);
//
//        self::$t2Repository->delete(1);
//        $this->assertNull(self::$t2Repository->findById(1));
//    }
//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testInvalidRelationalModelDelete(): void
//    {
//        $this->expectException(RepositoryException::class);
//        $t1 = new T1(1, "test");
//        $t2 = new T2(2, "test2", $t1);
//        self::$t1Repository->save($t1);
//        self::$t2Repository->save($t2);
//        self::$t1Repository->delete(1);
//    }
//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testValidRelationalModelUpdate(): void
//    {
//        $t1 = new T1(1, "testRelation");
//        self::$t1Repository->save($t1);
//        $t2 = new T2(1, "test2", $t1);
//        self::$t2Repository->save($t2);
//
//        $t2->v1 = "testUpdate";
//
//        self::$t2Repository->update($t2);
//
//        $this->assertEquals("testUpdate", self::$t2Repository->findById(1)->v1);
//        $this->assertEquals("testRelation", self::$t2Repository->findById(1)->t1->v1);
//    }
//
//    /**
//     * @return void
//     * @throws RepositoryException
//     */
//    public function testInvalidRelationalModelUpdate(): void
//    {
//        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
//        $t1 = new T1(99, "test");
//        $t2 = new T2(4, "test2", $t1);
//
//        self::$t2Repository->update($t2);
//    }
}
