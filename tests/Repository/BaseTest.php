<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Exceptions;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use AbstractRepo\Test\Models\T3;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class BaseTest extends TestCase
{
    /**
     * @var string
     */
    public const ENV_DSN = 'DB_DSN';
    /**
     * @var string
     */
    public const ENV_USERNAME = 'DB_USERNAME';
    /**
     * @var string
     */
    public const ENV_PASSWORD = 'DB_PASSWORD';

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
     * @var T4Repository
     */
    public static T4Repository $t4Repo;

    /**
     * @return void
     * @throws RepositoryException
     * @throws ReflectionException
     */
    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO(
            dsn: getenv(self::ENV_DSN),
            username: getenv(self::ENV_USERNAME),
            password: getenv(self::ENV_PASSWORD),
            options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => FALSE]
        );
        self::$pdo->exec(file_get_contents('./tests/test_schema.sql'));
        self::$t1Repo = new T1Repository(self::$pdo);
        self::$t2Repo = new T2Repository(self::$pdo);
        self::$t3Repo = new T3Repository(self::$pdo);
        self::$t4Repo = new T4Repository(self::$pdo);
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE T1;");
        self::$pdo->exec("TRUNCATE TABLE T2;");
        self::$pdo->exec("TRUNCATE TABLE T3;");
        self::$pdo->exec("TRUNCATE TABLE T4;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$pdo->exec(file_get_contents('./tests/drop_test_schema.sql'));
    }

    public function testDbConnection(): void
    {
        self::assertEquals('00000', self::$pdo->errorCode());
    }

}