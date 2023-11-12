<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Triggered during the mapping of relational objects
 */
final class ORMException extends Exception
{
    public const FAILED_MAPPING_OBJECT = "Failed mapping object";
    public const GENERIC_EXCEPTION = "Generic exception while mapping: ";
}