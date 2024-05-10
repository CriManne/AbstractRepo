<?php

namespace AbstractRepo\Test\Suites\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class BaseTestSuite extends TestCase
{
    /**
     * @var string
     */
    public const string ENV_DSN_TEST = 'DB_DSN_TEST';

    /**
     * @var string
     */
    public const string ENV_USERNAME = 'DB_USERNAME';

    /**
     * @var string
     */
    public const string ENV_PASSWORD = 'DB_PASSWORD';

    /**
     * @var string
     */
    public const string CREATE_TEST_SCHEMA_PATH = '/sql/create_test_schema.sql';

    /**
     * @var string
     */
    public const string DROP_TEST_SCHEMA_PATH = '/sql/drop_test_schema.sql';

    /**
     * @var PDO
     */
    public static PDO $pdo;

    public function __construct(string $name)
    {
        parent::__construct($name);

        self::$pdo = new PDO(
            dsn: getenv(self::ENV_DSN_TEST),
            username: getenv(self::ENV_USERNAME),
            password: getenv(self::ENV_PASSWORD),
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => FALSE
            ]
        );
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        $childReflectionClass = new ReflectionClass(get_called_class());
        $baseFilePath = dirname($childReflectionClass->getFileName());

        self::$pdo->exec(file_get_contents($baseFilePath . self::CREATE_TEST_SCHEMA_PATH));
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        $childReflectionClass = new ReflectionClass(get_called_class());
        $baseFilePath = dirname($childReflectionClass->getFileName());

        self::$pdo->exec(file_get_contents($baseFilePath . self::DROP_TEST_SCHEMA_PATH));
    }

    /**
     * @return void
     */
    public function testDbConnection(): void
    {
        self::assertEquals('00000', self::$pdo->errorCode());
    }
}