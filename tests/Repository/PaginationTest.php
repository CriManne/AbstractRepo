<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use AbstractRepo\Test\Models\T3;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use AbstractRepo\Exceptions;

class PaginationTest extends TestCase
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

        $this->assertEquals('test108', self::$t1Repo->findAll(2, 4)->getData()[0]->v1);
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

        $this->assertEquals(10, self::$t1Repo->findAll(2, 4)->getTotalPages());
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws RepositoryException
     * @throws Exceptions\ReflectionException
     */
    public function testOverPagination(): void
    {
        $this->assertEmpty(self::$t1Repo->findAll(2, 4)->getData());
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

        $this->assertCount(10, self::$t1Repo->findAll(0, 10)->getData());
        $this->assertEquals('test100', self::$t1Repo->findAll(0, 10)->getData()[0]->v1);
    }
}