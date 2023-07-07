<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Triggered during the mapping of relational objects
 */
final class ORMException extends Exception{

    static string $FAILED_MAPPING_OBJECT = "Failed mapping object";
    static string $GENERIC_EXCEPTION = "Generic exception while mapping: ";

}
?>