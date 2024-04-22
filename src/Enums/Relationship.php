<?php

declare(strict_types=1);

namespace AbstractRepo\Enums;

use AbstractRepo\Exceptions\EnumException;

/**
 * Identifies the types of FOREIGN KEY relationships
 * @TODO: Refactor, phpdocs, cleaning and optimize.
 */
enum Relationship
{
    case MANY_TO_ONE;
    case ONE_TO_ONE;

    /**
     * Return the enum relationships from the string value
     *
     * @param string $val
     * @return Relationship
     * @throws EnumException If the value passed is not valid as RelationshipException
     */
    static public function fromString(string $val): Relationship
    {
        foreach (self::cases() as $case) {
            if ($case->name == $val) return $case;
        }
        throw new EnumException(EnumException::INVALID_ENUM_VALUE);
    }
}