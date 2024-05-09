<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Simple;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\AbstractTestSuite;
use AbstractRepo\Test\Suites\Simple\Models\T1;
use AbstractRepo\Test\Suites\Simple\Repository\T1Repository;
use AbstractRepo\Test\Suites\Simple\Repository\TestInvalidModelRepository;

class SimpleTest extends AbstractTestSuite
{
    /**
     * @var T1Repository
     */
    public static T1Repository $t1Repo;

    /**
     * @throws RepositoryException
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        self::$t1Repo = new T1Repository(self::$pdo);
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE T1;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * @return T1[]
     */
    public static function providerT1Model(): array
    {
        return [
            [
                new T1(1, "test"),
                new T1(2, "test2", null),
            ]
        ];
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModel(): void
    {
        $this->expectException(RepositoryException::class);

        new TestInvalidModelRepository(self::$pdo);
    }

    /**
     * @dataProvider providerT1Model
     * @param T1 $t1
     * @return void
     * @throws RepositoryException
     */
    public function testModelSave(T1 $t1): void
    {
        self::expectNotToPerformAssertions();
        self::$t1Repo->save($t1);
    }

    /**
     * @dataProvider providerT1Model
     * @param T1 $t1
     * @return void
     * @throws RepositoryException
     */
    public function testFindById(T1 $t1): void
    {
        self::$t1Repo->save($t1);
        $this->assertEquals("test", self::$t1Repo->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindByWrongId(): void
    {
        $this->assertEquals(null, self::$t1Repo->findById(999));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindAll(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test");
            self::$t1Repo->save($t);
        }
        $this->assertCount(50, self::$t1Repo->find());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdate(): void
    {
        $t1 = new T1(1, "test");

        self::$t1Repo->save($t1);

        $t1->v1 = "testUpdate";

        self::$t1Repo->update($t1);

        $this->assertEquals("testUpdate", self::$t1Repo->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateWithWrongId(): void
    {
        $t1 = new T1(1, "test");

        self::$t1Repo->save($t1);

        $t1->id = 999;
        $t1->v1 = "testFailedUpdate";

        self::$t1Repo->update($t1);

        $this->assertEquals(null, self::$t1Repo->findById(999));
        $this->assertEquals("test", self::$t1Repo->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testDelete(): void
    {
        $t1 = new T1(1, "test");
        self::$t1Repo->save($t1);
        $this->assertNotEquals(null, self::$t1Repo->findById($t1->id));

        self::$t1Repo->delete($t1->id);
        $this->assertEquals(null, self::$t1Repo->findById($t1->id));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindParams(): void
    {
        $t1 = new T1(1, "testWhere");
        self::$t1Repo->save($t1);

        $this->assertNotNull(
            self::$t1Repo->find(
                new FetchParams(
                    conditions: "id = :id AND v1 = :v1",
                    bind: [
                        "id" => 1,
                        "v1" => "testWhere"
                    ]
                )
            )
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindInArray(): void
    {
        $t1 = new T1(1, "test");
        $t2 = new T1(2, "test2");
        self::$t1Repo->save($t1);
        self::$t1Repo->save($t2);

        $this->assertCount(
            2,
            self::$t1Repo->find(
                new FetchParams(
                    conditions: "id IN (:ids:array)",
                    bind: [
                        "ids" => [1,2,4]
                    ]
                )
            )
        );

        $this->assertEmpty(
            self::$t1Repo->find(
                new FetchParams(
                    conditions: "id IN (:ids:array)",
                    bind: [
                        "ids" => [523,6,4]
                    ]
                )
            )
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindFirst(): void
    {
        $t1 = new T1(1, "test");
        $t2 = new T1(2, "test");
        self::$t1Repo->save($t1);
        self::$t1Repo->save($t2);

        $this->assertEquals(
            1,
            self::$t1Repo->findFirst(
                new FetchParams(
                    conditions: "v1 LIKE :v1",
                    bind: [
                        "v1" => "%test%"
                    ]
                )
            )->id
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSearchByQuery(): void
    {
        $t1 = new T1(1, "testfoobar", "test");
        $t2 = new T1(2, "fooBAR00", "aaaa");
        $t3 = new T1(3, "foebat00", "foobar");
        $t4 = new T1(4, "testwrong", "wrongtest");
        
        self::$t1Repo->save($t1);
        self::$t1Repo->save($t2);
        self::$t1Repo->save($t3);
        self::$t1Repo->save($t4);

        $this->assertEquals(
            2,
            self::$t1Repo->findByQuery(
                query: "foo",
                page: 0,
                itemsPerPage: 10
            )->getData()[1]->id
        );

        $this->assertCount(
            1,
            self::$t1Repo->findByQuery(
                query: "aaa",
                page: 0,
                itemsPerPage: 10
            )->getData()
        );

        $this->assertEmpty(
            self::$t1Repo->findByQuery(
                query: "wrongsearchquery",
                page: 0,
                itemsPerPage: 10
            )->getData()
        );
    }
}