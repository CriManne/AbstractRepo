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

class AbstractRepositoryTest extends TestCase
{
    /**
     * @var string
     */
    public static string $dsnTest = "define-here-test-dsn";
    /**
     * @var string
     */
    public static string $username = "define-here-test-username";
    /**
     * @var string
     */
    public static string $password = "define-here-test-password";

    /**
     * @var PDO
     */
    public static PDO $pdo;

    /**
     * @var T1Repository
     */
    public static T1Repository $t1Repo;

    /**
     * @var T2Repository
     */
    public static T2Repository $t2Repo;

    /**
     * @var T3Repository
     */
    public static T3Repository $t3Repo;

    /**
     * @return void
     * @throws RepositoryException
     * @throws ReflectionException
     */
    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO(self::$dsnTest, self::$username, self::$password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => FALSE]);
        self::$pdo->exec(file_get_contents('./tests/test_schema.sql'));
        self::$t1Repo = new T1Repository(self::$pdo);
        self::$t2Repo = new T2Repository(self::$pdo);
        self::$t3Repo = new T3Repository(self::$pdo);
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE t1;");
        self::$pdo->exec("TRUNCATE TABLE t2;");
        self::$pdo->exec("TRUNCATE TABLE t3;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

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

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$pdo->exec(file_get_contents('./tests/drop_test_schema.sql'));
    }
}