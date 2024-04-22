<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\Utils;

class ArrayUtils
{
    /**
     * Returns the first item by the given filter callback function or null.
     *
     * @param callable $func
     * @param array $arr
     * @return mixed
     */
    public static function findFirstOrNull(callable $func, array $arr): mixed
    {
        $foundItems = array_filter($arr, $func);

        if (count($foundItems) == 0) {
            return null;
        }

        return array_values($foundItems)[0];
    }
}