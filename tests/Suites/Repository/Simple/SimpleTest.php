<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\AbstractTestSuite;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T1;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T2;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\T2Repository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\TestInvalidModelRepository;

class SimpleTest extends AbstractTestSuite
{
    /**
     * @var T1Repository
     */
    public static T1Repository $t1Repo;

    /**
     * @var T2Repository
     */
    public static T2Repository $t2Repo;

    /**
     * @throws RepositoryException
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        self::$t1Repo = new T1Repository(self::$pdo);
        self::$t2Repo = new T2Repository(self::$pdo);
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE T1;");
        self::$pdo->exec("TRUNCATE TABLE T2;");
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
     * @return T2[]
     */
    public static function providerT2Model(): array
    {
        return [
            [
                new T2("ID1", "test"),
                new T2("ID2", "test2"),
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

    /**
     * @dataProvider providerT2Model
     * @param T2 $t2
     * @return void
     * @throws RepositoryException
     */
    public function testSaveIdString(T2 $t2): void
    {
        self::expectNotToPerformAssertions();
        self::$t2Repo->save($t2);
    }

    /**
     * @dataProvider providerT2Model
     * @param T2 $t2
     * @return void
     * @throws RepositoryException
     */
    public function testFindByIdString(T2 $t2): void
    {
        self::$t2Repo->save($t2);
        $this->assertEquals('test', self::$t2Repo->findById("ID1")->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateIdString(): void
    {
        $t2 = new T2('admin@gmail.com', "test2");

        self::$t2Repo->save($t2);

        $t2->v1 = "test99";

        self::$t2Repo->update($t2);

        $this->assertEquals('test99', self::$t2Repo->findById('admin@gmail.com')->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testDeleteIdString(): void
    {
        $t2 = new T2('admin@gmail.com', "test2");
        self::$t2Repo->save($t2);
        $this->assertNotEquals(null, self::$t2Repo->findById($t2->id));
        self::$t2Repo->delete($t2->id);
        $this->assertEquals(null, self::$t2Repo->findById($t2->id));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidSingleModelPagination(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals('test108', self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getData()[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testTotalPages(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals(10, self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testOverPagination(): void
    {
        $this->assertEmpty(self::$t1Repo->find(new FetchParams(page: 2, itemsPerPage: 4))->getData());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFirstPagePagination(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertCount(10, self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getData());
        $this->assertEquals('test100', self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getData()[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testLowItemsPagination(): void
    {
        for ($i = 100; $i < 104; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals(1, self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testNoResultPagination(): void
    {
        $this->assertEmpty(self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getData());
        $this->assertEquals(0, self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 10))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testPaginationCountWithFilters(): void
    {
        for ($i = 100; $i < 240; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repo->save($t);
        }

        $this->assertEquals(15, self::$t1Repo->find(new FetchParams(page: 0, itemsPerPage: 1,conditions: "id >= 130 AND id < 145"))->getTotalPages());
    }
}