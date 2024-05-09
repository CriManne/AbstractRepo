<?php

namespace AbstractRepo\Test\Suites\Plugins\ORM;

use AbstractRepo\Exceptions\ORMException;
use AbstractRepo\Plugins\ORM\ORM;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ORMTest extends TestCase
{
    /**
     * @return void
     * @throws ORMException
     * @throws ReflectionException
     */
    public function testValid(): void
    {
        $arr = [
            "a" => "v1",
            "b" => "v2",
            "c" => 3
        ];

        $this->assertEquals(3, ORM::getNewInstance(Model::class, $arr)->c);
    }

    /**
     * @return void
     * @throws ORMException
     * @throws ReflectionException
     */
    public function testInvalidModelConstructor(): void
    {
        $this->expectException(ORMException::class);

        $arr = [
            "a" => "v1",
            "b" => "v2",
            "c" => 3
        ];

        ORM::getNewInstance(InvalidModel::class, $arr);
    }
}