<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;
use Exception;

/**
 * Triggered in the enum operations
 */
final class EnumException extends Exception{    

    public static string $INVALID_ENUM_VALUE = "Invalid enum value";

}

?>