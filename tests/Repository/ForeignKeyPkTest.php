<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use AbstractRepo\Test\Models\T3;
use AbstractRepo\Test\Models\T4;
use ReflectionException;

class ForeignKeyPkTest extends BaseTest
{
    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelFkPkSaveAndFindById(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repo->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repo->save($t4);

        $this->assertEquals('123', self::$t4Repo->findById('ABC')->t3->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidModelUpdate(): void
    {
        self::expectException(RepositoryException::class);

        $t3 = new T3('ABC', '123');
        $t3new = new T3('DEF', '345');

        self::$t3Repo->save($t3);
        self::$t3Repo->save($t3new);

        $t4 = new T4($t3, "test");

        self::$t4Repo->save($t4);

        $t4->t3 = $t3new;

        self::$t4Repo->update($t4);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testValidModelDelete(): void
    {
        $t3 = new T3('ABC', '123');

        self::$t3Repo->save($t3);

        $t4 = new T4($t3, "test");

        self::$t4Repo->save($t4);

        self::$t4Repo->delete('ABC');

        $this->assertEquals('123', self::$t3Repo->findById('ABC')->v1);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testInvalidDuplicatePk(): void
    {
        self::expectException(RepositoryException::class);

        $t3 = new T3('ABC', '123');

        self::$t3Repo->save($t3);

        $t4 = new T4($t3, "test");
        $t42 = new T4($t3, "test2");

        self::$t4Repo->save($t4);
        self::$t4Repo->save($t42);
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindAll(): void
    {
        $t31 = new T3('ABC', '123');
        $t32 = new T3('DEF', '456');

        self::$t3Repo->save($t31);
        self::$t3Repo->save($t32);

        $t41 = new T4($t31, "test1");
        $t42 = new T4($t32, "test2");

        self::$t4Repo->save($t41);
        self::$t4Repo->save($t42);

        $this->assertCount(2, self::$t4Repo->find());
    }

    /**
     * @return void
     * @throws RepositoryException
     */
    public function testFindBy(): void
    {
        $t31 = new T3('ABC', '123');
        $t32 = new T3('DEF', '456');

        self::$t3Repo->save($t31);
        self::$t3Repo->save($t32);

        $t41 = new T4($t31, "test1");
        $t42 = new T4($t32, "test2");

        self::$t4Repo->save($t41);
        self::$t4Repo->save($t42);

        $this->assertCount(
            1,
            self::$t4Repo->find(
                new FetchParams(
                    conditions: "t3_id = :t3Id",
                    bind: [
                        "t3Id" => "DEF"
                    ]
                )
            )
        );
    }
}