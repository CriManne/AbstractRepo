<?php

namespace AbstractRepo\Test\Suites\Repository\Generic;

use AbstractRepo\Test\Suites\Repository\Generic\Models\InvalidModelNoInterface;
use AbstractRepo\Test\Suites\Repository\Generic\Models\Model;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelNullable;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelPrivate;
use AbstractRepo\Test\Suites\Repository\Generic\Models\ModelRecursiveForeignKey;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\InvalidRepositoryNoInterface;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepository;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepositoryForeignKey;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepositoryNoPromoted;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepositoryNullable;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepositoryPrivate;
use AbstractRepo\Test\Suites\Repository\Generic\Repository\ValidRepositoryRecursiveForeignKey;
use Exception;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PDO;
use AbstractRepo\Exceptions\RepositoryException AS AbstractRepositoryException;
use PHPUnit;
use ReflectionClass;
use ReflectionException;

class AbstractRepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testValidRepository(): void
    {
        $this->expectNotToPerformAssertions();

        new ValidRepository($this->createMock(PDO::class));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testInvalidRepository(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        new InvalidRepositoryNoInterface($this->createMock(PDO::class));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testThrowExceptionInConstructor(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('setAttribute')->willThrowException(new Exception());

        new ValidRepository($mockPdo);
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testValidRepositoryWithForeignKey(): void
    {
        $this->expectNotToPerformAssertions();

        new ValidRepositoryForeignKey($this->createMock(PDO::class));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testValidRepositoryNoPromoted(): void
    {
        $this->expectNotToPerformAssertions();

        new ValidRepositoryNoPromoted($this->createMock(PDO::class));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     * @throws ReflectionException
     */
    public function testValidateRequestWrongModel(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $class = new ReflectionClass(ValidRepositoryForeignKey::class);
        $method = $class->getMethod('validateRequest');

        $method->invokeArgs(
            new ValidRepositoryForeignKey(
                $this->createMock(PDO::class)
            ),
            [new Model(1, "A")]
        );
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     * @throws ReflectionException
     */
    public function testGetPropertyValueRecursiveInvalidForeignKeyObject(): void
    {
        $this->expectExceptionMessage(AbstractRepositoryException::MODEL_IS_NOT_HANDLED);

        $class = new ReflectionClass(ValidRepositoryRecursiveForeignKey::class);
        $method = $class->getMethod('getPropertyValueRecursive');

        $method->invokeArgs(
            new ValidRepositoryRecursiveForeignKey(
                $this->createMock(PDO::class)
            ),
            [
                new ModelRecursiveForeignKey(
                    1,
                    new InvalidModelNoInterface("A","B")
                ),
                "val"
            ]
        );
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     * @throws ReflectionException
     */
    public function testGetItemCountsReturn0(): void
    {
        $class = new ReflectionClass(ValidRepository::class);
        $method = $class->getMethod('getItemsCount');

        $mockPdo = $this->createMock(PDO::class);

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('fetchAll')->willReturn([]);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $result = $method->invokeArgs(
            new ValidRepositoryRecursiveForeignKey($mockPdo),
            [
                "",
                null
            ]
        );

        $this->assertEquals(0, $result);
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     * @throws ReflectionException
     */
    public function testGetMappedObjectNull(): void
    {
        $class = new ReflectionClass(ValidRepository::class);
        $method = $class->getMethod('getMappedObject');

        $mockPdo = $this->createMock(PDO::class);
        $result = $method->invokeArgs(
            new ValidRepositoryForeignKey($mockPdo),
            [
                null,
                ""
            ]
        );

        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     * @throws ReflectionException
     */
    public function testGetMappedWrongObject(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $class = new ReflectionClass(ValidRepositoryPrivate::class);
        $method = $class->getMethod('getMappedObject');

        $mockPdo = $this->createMock(PDO::class);

        $method->invokeArgs(
            new ValidRepositoryPrivate($mockPdo),
            [
                [
                    "A" => "B"
                ],
                ModelPrivate::class
            ]
        );
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testFindStmtThrowError(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willThrowException(new Exception());

        $mockPdo = $this->createMock(PDO::class);

        $mockPdo->method('prepare')->willReturn($mockStatement);

        (new ValidRepository($mockPdo))
            ->find();
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     * @throws PHPUnit\Framework\MockObject\Exception
     */
    public function testFindByQueryStmtThrowError(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willThrowException(new Exception());

        $mockPdo = $this->createMock(PDO::class);

        $mockPdo->method('prepare')->willReturn($mockStatement);

        (new ValidRepository($mockPdo))
            ->findByQuery('A');
    }
}