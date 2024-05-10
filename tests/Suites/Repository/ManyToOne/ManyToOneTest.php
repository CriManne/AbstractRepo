<?php

namespace AbstractRepo\Test\Suites\Repository\ManyToOne;

use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T1;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Models\T2;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\ManyToOne\Repository\T2Repository;

class ManyToOneTest extends BaseTestSuite
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
    public function testModelSaveRelational(): void
    {
        $t1 = new T1(1, "testRelation");
        self::$t1Repository->save($t1);
        $t2 = new T2(1, "test2", $t1);
        self::$t2Repository->save($t2);

        $this->assertNotNull(self::$t2Repository->findById(1));
        $this->assertEquals("testRelation", self::$t2Repository->findById(1)->t1->v1);
    }
}
