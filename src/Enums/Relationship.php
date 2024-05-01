<?php

declare(strict_types=1);

namespace AbstractRepo\Enums;

/**
 * Identifies the types of FOREIGN KEY relationships
 */
enum Relationship
{
    case MANY_TO_ONE;
    case ONE_TO_ONE;
}