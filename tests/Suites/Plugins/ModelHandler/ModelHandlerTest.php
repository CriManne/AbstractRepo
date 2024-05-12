<?php

namespace AbstractRepo\Test\Suites\Plugins\ModelHandler;

use AbstractRepo\DataModels\FieldInfo;
use AbstractRepo\Plugins\ModelHandler\ModelHandler;
use PHPUnit\Framework\TestCase;

class ModelHandlerTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function providerFieldInfo(): array
    {
        return [
            [
                new FieldInfo(
                    propertyName: "FIELD1",
                    propertyType: "string",
                    isRequired: true,
                    allowsNull: false,
                    isPrimaryKey: true,
                    autoIncrement: false,
                    isForeignKey: false,
                    defaultValue: null
                )
            ]
        ];
    }

    /**
     * @dataProvider providerFieldInfo
     * @param FieldInfo $fieldInfo
     * @return void
     */
    public function testSave(FieldInfo $fieldInfo): void
    {
        $this->expectNotToPerformAssertions();
        $modelHandler = new ModelHandler();

        $modelHandler->save(
            "FIELD1",
            $fieldInfo
        );
    }

    /**
     * @dataProvider providerFieldInfo
     * @param FieldInfo $fieldInfo
     * @return void
     */
    public function testGetPrimaryKey(FieldInfo $fieldInfo): void
    {
        $modelHandler = new ModelHandler();

        $modelHandler->save(
            "FIELD1",
            $fieldInfo
        );

        $this->assertEquals("FIELD1", $modelHandler->getKey()->propertyName);
    }
}