<?php

namespace AbstractRepo\Test\Suites;

use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class AbstractTestSuite extends TestCase
{
    /**
     * @var string
     */
    public const string ENV_DSN = 'DB_DSN';

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
     * The file path of the child class
     * @var string
     */
    public static string $baseFilePath;

    /**
     * @var PDO
     */
    public static PDO $pdo;

    public function __construct(string $name)
    {
        parent::__construct($name);

        self::$pdo = new PDO(
            dsn: getenv(self::ENV_DSN),
            username: getenv(self::ENV_USERNAME),
            password: getenv(self::ENV_PASSWORD),
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => FALSE
            ]
        );

        $childReflectionClass = new ReflectionClass(get_class($this));

        self::$baseFilePath = dirname($childReflectionClass->getFileName());
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$pdo->exec(file_get_contents(self::$baseFilePath . self::CREATE_TEST_SCHEMA_PATH));
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$pdo->exec(file_get_contents(self::$baseFilePath . self::DROP_TEST_SCHEMA_PATH));
    }

    /**
     * @return void
     */
    public function testDbConnection(): void
    {
        self::assertEquals('00000', self::$pdo->errorCode());
    }
}