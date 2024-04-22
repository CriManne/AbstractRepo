<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\ORM;

use AbstractRepo\Exceptions;
use AbstractRepo\Interfaces;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Utility class used for ORM operations
 * @TODO: Refactor, phpdocs, cleaning and optimize.
 */
final class ORM{

    /**
     * Return a new instance of the class passed as string with the params passed as array
     * @param string $className The class name of the new instance
     * @param array $obj The params to fill the class with
     * @return Interfaces\IModel The new instance of the object
     * @throws Exceptions\ORMException
     * @throws ReflectionException
     */
    public static function getNewInstance(string $className, array $obj): Interfaces\IModel {
        $reflectionClass = new ReflectionClass($className);
        try{
            $obj = $reflectionClass->newInstanceArgs($obj);
            if(is_null($obj)) throw new Exceptions\ORMException(Exceptions\ORMException::FAILED_MAPPING_OBJECT);
                        
            return $obj;
        }catch(Exception $ex){
            throw new Exceptions\ORMException(Exceptions\ORMException::GENERIC_EXCEPTION.$ex->getMessage());
        }
    }
}