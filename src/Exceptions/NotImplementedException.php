<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Used in development, identify a function that is not been implemented yet
 * @TODO: Refactor, phpdocs, cleaning and optimize.
 */
final class NotImplementedException extends Exception
{
    public const NOT_IMPLEMENTED = "This function is not implemented!";

    public function __construct(string $message = NotImplementedException::NOT_IMPLEMENTED)
    {
        parent::__construct($message);
    }
}