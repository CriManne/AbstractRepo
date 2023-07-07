<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;
use Exception;

/**
 * Used in development, identify a function that is not been implemented yet
 */
final class NotImplementedException extends Exception{    

    static string $NOT_IMPLEMENTED = "This function is not implemented!";

    function __construct(string $message = NotImplementedException::$NOT_IMPLEMENTED){
        parent::__construct($message);
    }

}

?>