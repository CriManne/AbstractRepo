<?php

namespace AbstractRepo\Test\Suites\Plugins\Reflection;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\Searchable;
use AbstractRepo\Plugins\Reflection\ReflectionUtility;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use AbstractRepo\Exceptions\ReflectionException AS AbstractReflectionException;
use AbstractRepo\Exceptions\RepositoryException AS AbstractRepositoryException;
class ReflectionUtilityTest extends TestCase
{
    /**
     * @return void
     * @throws AbstractReflectionException
     * @throws ReflectionException
     */
    public function testGetPrimaryKey(): void
    {
        $this->assertEquals("id", ReflectionUtility::getPrimaryKeyProperty(Model::class)->getName());
    }

    /**
     * @return void
     * @throws AbstractReflectionException
     * @throws ReflectionException
     */
    public function testInvalidGetPrimaryKey(): void
    {
        $this->expectException(AbstractReflectionException::class);
        ReflectionUtility::getPrimaryKeyProperty(InvalidModelNoPk::class);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testGetForeignKey(): void
    {
        $this->assertCount(2, ReflectionUtility::getForeignKeyProperties(Model::class));
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testGetPropertyWithAttribute(): void
    {
        $this->assertCount(2, ReflectionUtility::getPropertyWithAttribute(Model::class, Searchable::class));
    }

    /**
     * @return void
     * @throws AbstractReflectionException
     * @throws ReflectionException
     */
    public function testGetProperty(): void
    {
        $this->assertNotNull(ReflectionUtility::getProperty(Model::class, 'v2'));
    }

    /**
     * @return void
     * @throws AbstractReflectionException
     * @throws ReflectionException
     */
    public function testGetPropertyNotFound(): void
    {
        $this->expectException(AbstractReflectionException::class);
        $this->assertNotNull(ReflectionUtility::getProperty(Model::class, 'vNotFound'));
    }

    /**
     * @return void
     */
    public function testGetAttribute(): void
    {
        $reflectionClass = new ReflectionClass(Model::class);

        $this->assertNotNull(ReflectionUtility::getAttribute($reflectionClass, Entity::class));
    }

    /**
     * @return void
     */
    public function testGetAttributeNotFound(): void
    {
        $reflectionClass = new ReflectionClass(Model::class);

        $this->assertNull(ReflectionUtility::getAttribute($reflectionClass, Searchable::class));
    }

    /**
     * @return void
     */
    public function testGetAttributeNoAttributes(): void
    {
        $reflectionClass = new ReflectionClass(InvalidModelNoAttributes::class);

        $this->assertNull(ReflectionUtility::getAttribute($reflectionClass, Searchable::class));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     */
    public function testGetTableName(): void
    {
        $reflectionClass = new ReflectionClass(Model::class);

        $this->assertEquals('Model', ReflectionUtility::getTableName($reflectionClass));
    }

    /**
     * @return void
     * @throws AbstractRepositoryException
     */
    public function testGetTableNameNotFound(): void
    {
        $this->expectException(AbstractRepositoryException::class);

        $reflectionClass = new ReflectionClass(InvalidModelNoAttributes::class);
        ReflectionUtility::getTableName($reflectionClass);
    }

    /**
     * @return void
     */
    public function testGetConstructorParams(): void
    {
        $reflectionClass = new ReflectionClass(Model::class);

        $this->assertNotNull(ReflectionUtility::getConstructorParameter($reflectionClass, 'v2'));
    }
}