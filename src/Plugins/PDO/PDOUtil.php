<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\PDO;

use PDO;

class PDOUtil
{
    /**
     * Returns the PDO type from the PHP type.
     *
     * @param string $type
     * @return int
     */
    public static function getPDOType(string $type): int
    {
        return match ($type) {
            'int', '?int', 'integer' => PDO::PARAM_INT,
            '?string', 'string' => PDO::PARAM_STR,
            default => PDO::PARAM_STR,
        };
    }
}