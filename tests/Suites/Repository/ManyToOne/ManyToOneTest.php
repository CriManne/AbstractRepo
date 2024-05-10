<?php

namespace AbstractRepo\Test\Suites\Repository\ManyToOne;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T1;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T2;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T3;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T4;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T5;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T2Repository;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T3Repository;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T4Repository;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T5Repository;

class ManyToOneTest extends BaseTestSuite
{
    public static T1Repository $t1Repository;
    public static T2Repository $t2Repository;
    public static T3Repository $t3Repository;
    public static T4Repository $t4Repository;
    public static T5Repository $t5Repository;

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
        self::$pdo->exec("TRUNCATE TABLE T3;");
        self::$pdo->exec("TRUNCATE TABLE T4;");
        self::$pdo->exec("TRUNCATE TABLE T5;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        self::$t1Repository = new T1Repository(self::$pdo);
        self::$t2Repository = new T2Repository(self::$pdo);
        self::$t3Repository = new T3Repository(self::$pdo);
        self::$t4Repository = new T4Repository(self::$pdo);
        self::$t5Repository = new T5Repository(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testModelSave(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test", $t1);
        self::$t2Repository->save($t2);

        $this->assertNotNull(self::$t2Repository->findById(1));
        $this->assertEquals("testRelation", self::$t2Repository->findById(1)->t1->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testRelatedObjectNotFound(): void
    {
        self::expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);

        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test", $t1);
        self::$t2Repository->save($t2);

        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        self::$t1Repository->delete($t1->id);
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        self::$t2Repository->findById(1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelSave(): void
    {
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(9999, "test");
        $t2 = new T2(2, "test", $t1);
        self::$t2Repository->save($t2);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testModelDelete(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repository->save($t2);

        self::$t2Repository->delete(1);
        $this->assertNull(self::$t2Repository->findById(1));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidRelationalModelDelete(): void
    {
        $this->expectException(RepositoryException::class);
        $t1 = new T1(1, "test");
        $t2 = new T2(2, "test2", $t1);
        self::$t1Repository->save($t1);
        self::$t2Repository->save($t2);
        self::$t1Repository->delete(1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidRelationalModelUpdate(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repository->save($t2);

        $t2->v1 = "testUpdate";

        self::$t2Repository->update($t2);

        $this->assertEquals("testUpdate", self::$t2Repository->findById(1)->v1);
        $this->assertEquals("testRelation", self::$t2Repository->findById(1)->t1->v1);
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

        self::$t2Repository->update($t2);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelForeignKeyAsPrimaryKeySave(): void
    {
        $this->expectNotToPerformAssertions();
        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repository->save($t4);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelForeignKeyAsPrimaryKeyFindById(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repository->save($t4);

        $this->assertEquals('123', self::$t4Repository->findById('ABC')->t3->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelForeignKeyAsPrimaryKeyUpdate(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repository->save($t4);

        $t4->v1 = "test99";

        self::$t4Repository->update($t4);

        $this->assertEquals('123', self::$t4Repository->findByQuery('test99')[0]->t3->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelForeignKeyAsPrimaryKeyDelete(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repository->save($t4);

        self::$t4Repository->delete('ABC');

        $this->assertEquals('123', self::$t3Repository->findById('ABC')->v1);
        $this->assertNull(self::$t4Repository->findById('ABC'));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidDuplicateForeignKeyAsPrimaryKeySave(): void
    {
        self::expectException(RepositoryException::class);

        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");
        $t42 = new T4($t3, "test2");

        self::$t4Repository->save($t4);
        self::$t4Repository->save($t42);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindAll(): void
    {
        $t31 = new T3('ABC', '123');
        $t32 = new T3('DEF', '456');
        $t41 = new T4($t31, "test1");
        $t42 = new T4($t32, "test2");

        self::$t3Repository->save($t31);
        self::$t3Repository->save($t32);
        self::$t4Repository->save($t41);
        self::$t4Repository->save($t42);

        $this->assertCount(2, self::$t4Repository->find());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindBy(): void
    {
        $t31 = new T3('ABC', '123');
        $t32 = new T3('DEF', '456');
        $t41 = new T4($t31, "test1");
        $t42 = new T4($t32, "test2");

        self::$t3Repository->save($t31);
        self::$t3Repository->save($t32);
        self::$t4Repository->save($t41);
        self::$t4Repository->save($t42);

        $this->assertEquals(
            '456',
            self::$t4Repository->find(
                new FetchParams(
                    conditions: "t3_id = :t3Id",
                    bind: [
                        "t3Id" => "DEF"
                    ]
                )
            )[0]->t3->v1
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindByQueryOnForeignKeyAsPrimaryKey(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repository->save($t4);

        $this->assertCount(1, self::$t4Repository->findByQuery('ABC'));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSaveThreeLevelNesting(): void
    {
        $t3 = new T3('abc', 'val3');

        self::$t3Repository->save($t3);

        $t4 = new T4($t3, 'val4');

        self::$t4Repository->save($t4);

        $t5 = new T5($t4, 'val5');

        self::$t5Repository->save($t5);

        self::assertEquals('val3', self::$t5Repository->findById('abc')->t4->t3->v1);
    }
}
