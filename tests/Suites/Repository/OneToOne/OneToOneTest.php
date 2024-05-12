<?php

namespace AbstractRepo\Test\Suites\Repository\OneToOne;

use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\OneToOne\Models\T1;
use AbstractRepo\Test\Suites\Repository\OneToOne\Models\T2;
use AbstractRepo\Test\Suites\Repository\OneToOne\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\OneToOne\Repository\T2Repository;

class OneToOneTest extends BaseTestSuite
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
    public function testModelSave(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, $t1);
        self::$t2Repository->save($t2);

        $this->assertNotNull(self::$t2Repository->findById(1));
        $this->assertEquals("testRelation", self::$t2Repository->findById(1)->t1->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidRelationalModelUpdate(): void
    {
        $t1 = new T1(1, "testRelation");
        $t12 = new T1(2, "testRelation2");
        $t2 = new T2(1, $t1);

        self::$t1Repository->save($t1);
        self::$t1Repository->save($t12);

        self::$t2Repository->save($t2);

        $t2->t1 = $t12;

        self::$t2Repository->update($t2);

        $this->assertEquals("testRelation2", self::$t2Repository->findById(1)->t1->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testModelDelete(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, $t1);
        self::$t2Repository->save($t2);

        self::$t2Repository->delete(1);
        $this->assertNull(self::$t2Repository->findById(1));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidOneToOneModelSave(): void
    {
        $this->expectException(RepositoryException::class);
        $t1 = new T1(1, "testRelation");
        $t2 = new T2(1, $t1);
        $t22 = new T2(2, $t1);

        self::$t1Repository->save($t1);
        self::$t2Repository->save($t2);
        self::$t2Repository->save($t22);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidOneToOneModelUpdate(): void
    {
        $this->expectException(RepositoryException::class);
        $t1 = new T1(1, "testRelation");
        $t12 = new T1(2, "testRelation2");
        $t2 = new T2(1, $t1);
        $t22 = new T2(2, $t12);

        self::$t1Repository->save($t1);
        self::$t1Repository->save($t12);
        self::$t2Repository->save($t2);
        self::$t2Repository->save($t22);

        $t22->t1 = $t1;

        self::$t2Repository->update($t22);
    }
}
