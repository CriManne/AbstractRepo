<?php

namespace AbstractRepo\Test\Suites\Repository\FindByQuery;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Models\T1;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Models\T2;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Models\T3;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Repository\T2Repository;
use AbstractRepo\Test\Suites\Repository\FindByQuery\Repository\T3Repository;

class FindByQueryTest extends BaseTestSuite
{
    public static T1Repository $t1Repository;
    public static T2Repository $t2Repository;
    public static T3Repository $t3Repository;

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
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        self::$t1Repository = new T1Repository(self::$pdo);
        self::$t2Repository = new T2Repository(self::$pdo);
        self::$t3Repository = new T3Repository(self::$pdo);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFirstLevelFound(): void
    {
        $t10 = new T1(1, "house");
        $t11 = new T1(2, "chocolate");
        $t12 = new T1(3, "foobar");

        self::$t1Repository->save($t10);
        self::$t1Repository->save($t11);
        self::$t1Repository->save($t12);

        $this->assertCount(2, self::$t1Repository->findByQuery("ho"));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFirstLevelNotFound(): void
    {
        $t10 = new T1(1, "house");
        $t11 = new T1(2, "chocolate");
        $t12 = new T1(3, "foobar");

        self::$t1Repository->save($t10);
        self::$t1Repository->save($t11);
        self::$t1Repository->save($t12);

        $this->assertCount(0, self::$t1Repository->findByQuery("bee"));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSecondLevelFound(): void
    {
        $t10 = new T1(1, "house");
        $t11 = new T1(2, "chocolate");
        $t12 = new T1(3, "foobar");

        self::$t1Repository->save($t10);
        self::$t1Repository->save($t11);
        self::$t1Repository->save($t12);

        $t20 = new T2(1, 'aaa', $t10);
        $t21 = new T2(2, 'bbb', $t11);
        $t22 = new T2(3, 'ccc', $t12);

        self::$t2Repository->save($t20);
        self::$t2Repository->save($t21);
        self::$t2Repository->save($t22);

        $this->assertCount(2, self::$t2Repository->findByQuery("ho"));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSecondLevelNotFound(): void
    {
        $t10 = new T1(1, "house");
        $t11 = new T1(2, "chocolate");
        $t12 = new T1(3, "foobar");

        self::$t1Repository->save($t10);
        self::$t1Repository->save($t11);
        self::$t1Repository->save($t12);

        $t20 = new T2(1, 'aaa', $t10);
        $t21 = new T2(2, 'bbb', $t11);
        $t22 = new T2(3, 'ccc', $t12);

        self::$t2Repository->save($t20);
        self::$t2Repository->save($t21);
        self::$t2Repository->save($t22);

        $this->assertCount(0, self::$t2Repository->findByQuery("foobar"));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testThirdLevelLimitCheck(): void
    {
        $t10 = new T1(1, "house");
        $t11 = new T1(2, "chocolate");
        $t12 = new T1(3, "foobar");

        self::$t1Repository->save($t10);
        self::$t1Repository->save($t11);
        self::$t1Repository->save($t12);

        $t20 = new T2(1, 'aaa', $t10);
        $t21 = new T2(2, 'bbb', $t11);
        $t22 = new T2(3, 'ccc', $t12);

        self::$t2Repository->save($t20);
        self::$t2Repository->save($t21);
        self::$t2Repository->save($t22);

        $t30 = new T3(1, 'ddd', $t20);
        $t31 = new T3(2, 'eee', $t21);
        $t32 = new T3(3, 'fff', $t22);

        self::$t3Repository->save($t30);
        self::$t3Repository->save($t31);
        self::$t3Repository->save($t32);

        $this->assertCount(0, self::$t2Repository->findByQuery("house"));
    }
}
