<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Triggered in reflection operations
 */
final class ReflectionException extends Exception
{
    public const string PROPERTY_NOT_FOUND = "Property not found";
}