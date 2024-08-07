<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Suites\Repository\Simple;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Suites\Repository\BaseTestSuite;
use AbstractRepo\Test\Suites\Repository\Simple\Models\Order;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T1;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T2;
use AbstractRepo\Test\Suites\Repository\Simple\Models\T3;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\OrderRepository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\T1Repository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\T2Repository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\T3Repository;
use AbstractRepo\Test\Suites\Repository\Simple\Repository\TestInvalidModelRepository;

class SimpleTest extends BaseTestSuite
{
    public static T1Repository    $t1Repository;
    public static T2Repository    $t2Repository;
    public static T3Repository    $t3Repository;
    public static OrderRepository $orderRepository;

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
        self::$pdo->exec("TRUNCATE TABLE `Order`;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        self::$t1Repository    = new T1Repository(self::$pdo);
        self::$t2Repository    = new T2Repository(self::$pdo);
        self::$t3Repository    = new T3Repository(self::$pdo);
        self::$orderRepository = new OrderRepository(self::$pdo);
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
     *
     * @param T1 $t1
     *
     * @return void
     * @throws RepositoryException
     */
    public function testModelSave(T1 $t1): void
    {
        self::expectNotToPerformAssertions();
        self::$t1Repository->save($t1);
    }

    public function testSaveModelWithoutRequiredData(): void
    {
        self::expectNotToPerformAssertions(RepositoryException::class);

        $t1 = new T1(null, "A", "B");

        self::$t1Repository->save($t1);
    }

    /**
     * @dataProvider providerT1Model
     *
     * @param T1 $t1
     *
     * @return void
     * @throws RepositoryException
     */
    public function testFindById(T1 $t1): void
    {
        self::$t1Repository->save($t1);
        $this->assertEquals("test", self::$t1Repository->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindByWrongId(): void
    {
        $this->assertEquals(null, self::$t1Repository->findById(999));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindAll(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test");
            self::$t1Repository->save($t);
        }
        $this->assertCount(50, self::$t1Repository->find());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdate(): void
    {
        $t1 = new T1(1, "test");

        self::$t1Repository->save($t1);

        $t1->v1 = "testUpdate";

        self::$t1Repository->update($t1);

        $this->assertEquals("testUpdate", self::$t1Repository->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateWithWrongId(): void
    {
        $t1 = new T1(1, "test");

        self::$t1Repository->save($t1);

        $t1->id = 999;
        $t1->v1 = "testFailedUpdate";

        self::$t1Repository->update($t1);

        $this->assertEquals(null, self::$t1Repository->findById(999));
        $this->assertEquals("test", self::$t1Repository->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testDelete(): void
    {
        $t1 = new T1(1, "test");
        self::$t1Repository->save($t1);
        $this->assertNotEquals(null, self::$t1Repository->findById($t1->id));

        self::$t1Repository->delete($t1->id);
        $this->assertEquals(null, self::$t1Repository->findById($t1->id));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindParams(): void
    {
        $t1 = new T1(1, "testWhere");
        self::$t1Repository->save($t1);

        $this->assertNotNull(
            self::$t1Repository->find(
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
        self::$t1Repository->save($t1);
        self::$t1Repository->save($t2);

        $this->assertCount(
            2,
            self::$t1Repository->find(
                new FetchParams(
                    conditions: "id IN (:ids:array)",
                    bind: [
                        "ids" => [1, 2, 4]
                    ]
                )
            )
        );

        $this->assertEmpty(
            self::$t1Repository->find(
                new FetchParams(
                    conditions: "id IN (:ids:array)",
                    bind: [
                        "ids" => [523, 6, 4]
                    ]
                )
            )
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testArrayPlaceholderInString(): void
    {
        $t1 = new T1(1, ":ids:array");
        self::$t1Repository->save($t1);

        $this->assertCount(
            1,
            self::$t1Repository->find(
                new FetchParams(
                    conditions: "v1 = ':ids:array'"
                )
            )
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindByQueryNoSearchableFields(): void
    {
        $t2 = new T2("A", "B");
        self::$t2Repository->save($t2);

        $this->assertEmpty(self::$t2Repository->findByQuery("A"));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testEmptyFindFirst(): void
    {
        $this->assertNull(self::$t2Repository->findFirst());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindFirst(): void
    {
        $t1 = new T1(1, "test");
        $t2 = new T1(2, "test");
        self::$t1Repository->save($t1);
        self::$t1Repository->save($t2);

        $this->assertEquals(
            1,
            self::$t1Repository->findFirst(
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

        self::$t1Repository->save($t1);
        self::$t1Repository->save($t2);
        self::$t1Repository->save($t3);
        self::$t1Repository->save($t4);

        $this->assertEquals(
            2,
            self::$t1Repository->findByQuery(
                query: "foo",
                params: new FetchParams(page:0, itemsPerPage: 10)
            )->getData()[1]->id
        );

        $this->assertCount(
            1,
            self::$t1Repository->findByQuery(
                query: "aaa",
                params: new FetchParams(page:0, itemsPerPage: 10)
            )->getData()
        );

        $this->assertEmpty(
            self::$t1Repository->findByQuery(
                query: "wrongsearchquery",
                params: new FetchParams(page:0, itemsPerPage: 10)
            )->getData()
        );
    }

    /**
     * @dataProvider providerT2Model
     *
     * @param T2 $t2
     *
     * @return void
     * @throws RepositoryException
     */
    public function testSaveIdString(T2 $t2): void
    {
        self::expectNotToPerformAssertions();
        self::$t2Repository->save($t2);
    }

    /**
     * @dataProvider providerT2Model
     *
     * @param T2 $t2
     *
     * @return void
     * @throws RepositoryException
     */
    public function testFindByIdString(T2 $t2): void
    {
        self::$t2Repository->save($t2);
        $this->assertEquals('test', self::$t2Repository->findById("ID1")->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateIdString(): void
    {
        $t2 = new T2('admin@gmail.com', "test2");

        self::$t2Repository->save($t2);

        $t2->v1 = "test99";

        self::$t2Repository->update($t2);

        $this->assertEquals('test99', self::$t2Repository->findById('admin@gmail.com')->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testDeleteIdString(): void
    {
        $t2 = new T2('admin@gmail.com', "test2");
        self::$t2Repository->save($t2);
        $this->assertNotEquals(null, self::$t2Repository->findById($t2->id));
        self::$t2Repository->delete($t2->id);
        $this->assertEquals(null, self::$t2Repository->findById($t2->id));
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidSingleModelPagination(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals('test108', self::$t1Repository->find(new FetchParams(page: 2, itemsPerPage: 4))->getData()[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testTotalPages(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals(10, self::$t1Repository->find(new FetchParams(page: 2, itemsPerPage: 4))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testOverPagination(): void
    {
        $this->assertEmpty(self::$t1Repository->find(new FetchParams(page: 2, itemsPerPage: 4))->getData());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFirstPagePagination(): void
    {
        for ($i = 100; $i < 140; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertCount(10, self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 10))->getData());
        $this->assertEquals('test100', self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 10))->getData()[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testLowItemsPagination(): void
    {
        for ($i = 100; $i < 104; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals(1, self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 10))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testNoResultPagination(): void
    {
        $this->assertEmpty(self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 10))->getData());
        $this->assertEquals(0, self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 10))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testPaginationCountWithFilters(): void
    {
        for ($i = 100; $i < 240; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals(15, self::$t1Repository->find(new FetchParams(page: 0, itemsPerPage: 1, conditions: "id >= 130 AND id < 145"))->getTotalPages());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSaveNullModel(): void
    {
        $this->expectNotToPerformAssertions();

        $t = new T3();
        self::$t3Repository->save($t);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateNullModel(): void
    {
        $t = new T3();
        self::$t3Repository->save($t);

        $t->v1 = "AB";

        self::$t3Repository->update($t);

        self::assertEquals("AB", self::$t3Repository->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testUpdateToNullNullableField(): void
    {
        $t = new T1(100, "test", "value");
        self::$t1Repository->save($t);

        $t->v2 = null;
        self::$t1Repository->update($t);

        self::assertEquals(null, self::$t1Repository->findById($t->id)->v2);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelSave(): void
    {
        self::expectNotToPerformAssertions();

        $order = new Order(1, "test");
        self::$orderRepository->save($order);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelFindById(): void
    {
        $order = new Order(1, "test");
        self::$orderRepository->save($order);

        self::assertEquals("test", self::$orderRepository->findById(1)->v1);
    }

    /**
     * @depends testKeywordModelFindById
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelUpdate(): void
    {
        $order = new Order(1, "test");
        self::$orderRepository->save($order);

        $order->v1 = "update";
        self::$orderRepository->update($order);


        self::assertEquals("update", self::$orderRepository->findById(1)->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelFindByQuery(): void
    {
        $order = new Order(1, "test");
        self::$orderRepository->save($order);

        self::assertEquals("test", self::$orderRepository->findByQuery("es")[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelFindFirst(): void
    {
        $order = new Order(1, "test");
        self::$orderRepository->save($order);

        self::assertEquals("test", self::$orderRepository->findFirst(new FetchParams(conditions: "v1 LIKE :query", bind: ["query" => "%est%"]))->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelFind(): void
    {
        for ($i = 100; $i < 240; $i++) {
            $order = new Order($i, "test" . $i);
            self::$orderRepository->save($order);
        }

        self::assertEquals(15, self::$orderRepository->find(new FetchParams(page: 0, itemsPerPage: 1, conditions: "id >= 130 AND id < 145"))->getTotalPages());
    }

    /**
     * @depends testKeywordModelSave
     * @return void
     * @throws RepositoryException
     */
    public function testKeywordModelDelete(): void
    {
        $this->expectNotToPerformAssertions();
        $order = new Order(1, "test");
        self::$orderRepository->save($order);

        self::$orderRepository->delete(1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testOrderBy(): void
    {
        $t21 = new T2("AAA", "aword");
        $t22 = new T2("BBB", "bword");
        $t23 = new T2("CCC", "cword");

        self::$t2Repository->save($t21);
        self::$t2Repository->save($t22);
        self::$t2Repository->save($t23);

        self::assertEquals("cword", self::$t2Repository->find(new FetchParams(orderBy: ["v1 DESC"]))[0]->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testOrderByMultiple(): void
    {
        $t21 = new T2("AAA", "aword");
        $t22 = new T2("BBB", "aword");
        $t23 = new T2("CCC", "cword");

        self::$t2Repository->save($t21);
        self::$t2Repository->save($t22);
        self::$t2Repository->save($t23);

        self::assertEquals("BBB", self::$t2Repository->find(new FetchParams(orderBy: ["v1 DESC", "id DESC"]))[1]->id);
    }


    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSearchByQueryMoreConditions(): void
    {
        $t1 = new T1(1, "testfoobar", "test");
        $t2 = new T1(2, "fooBAR00", "aaaa");
        $t3 = new T1(3, "foebar00", "foobar");
        $t4 = new T1(4, "testwrong", "foobar");

        self::$t1Repository->save($t1);
        self::$t1Repository->save($t2);
        self::$t1Repository->save($t3);
        self::$t1Repository->save($t4);

        $this->assertCount(
            2,
            self::$t1Repository->findByQuery(
                query: "foo",
                params: new FetchParams(
                    page: 0,
                    itemsPerPage: 10,
                    conditions: "v2 = :v2",
                    bind: ["v2" => "foobar"]
                )
            )->getData()
        );

        $this->assertCount(
            1,
            self::$t1Repository->findByQuery(
                query: "foebar",
                params: new FetchParams(
                    page: 0,
                    itemsPerPage: 10,
                    conditions: "v2 = :v2",
                    bind: ["v2" => "foobar"]
                )
            )->getData()
        );

        $this->assertCount(
            2,
            self::$t1Repository->findByQuery(
                query: "foo",
                params: new FetchParams(
                    page: 0,
                    itemsPerPage: 10,
                    conditions: "v2 = :v2 AND v2 <> :v2not",
                    bind: ["v2" => "foobar", "v2not" => "aaaa"]
                )
            )->getData()
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSortingFind(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals(
            'test141',
            self::$t1Repository->find(
                new FetchParams(
                    page: 2,
                    itemsPerPage: 4,
                    orderBy: ['v1 DESC']
                )
            )->getData()[0]->v1
        );
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testSortingFindByQuery(): void
    {
        for ($i = 100; $i < 150; $i++) {
            $t = new T1($i, "test" . $i);
            self::$t1Repository->save($t);
        }

        $this->assertEquals(
            'test141',
            self::$t1Repository->findByQuery(
                'test',
                new FetchParams(
                    page: 2,
                    itemsPerPage: 4,
                    orderBy: ['v1 DESC']
                )
            )->getData()[0]->v1
        );
    }
}