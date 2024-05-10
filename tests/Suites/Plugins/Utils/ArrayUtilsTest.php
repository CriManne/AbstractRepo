<?php

namespace AbstractRepo\Test\Suites\Plugins\Utils;

use AbstractRepo\Plugins\Utils\ArrayUtils;
use PHPUnit\Framework\TestCase;

class ArrayUtilsTest extends TestCase
{
    /**
     * @return void
     */
    public function testFindFirst(): void
    {
        $arr = [1,2,3,4,5];

        $this->assertEquals(4, ArrayUtils::findFirstOrNull(fn($a) => $a > 3, $arr));
    }

    /**
     * @return void
     */
    public function testFindFirstNull(): void
    {
        $arr = [1,2,3,4,5];

        $this->assertNull(ArrayUtils::findFirstOrNull(fn($a) => $a > 30, $arr));
    }
}