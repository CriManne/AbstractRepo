<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Triggered in reflection operations
 * @TODO: Refactor, phpdocs, cleaning and optimize.
 */
final class ReflectionException extends Exception
{
    public const PROPERTY_NOT_FOUND = "Property not found";
    public const MANAGED_MODEL_NOT_FOUND = "Managed model not found";
}