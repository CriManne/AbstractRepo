<?php

declare(strict_types=1);

namespace AbstractRepo\Util;

use AbstractRepo\Exceptions\ORMException;
use AbstractRepo\Interfaces\IModel;
use Exception;
use ReflectionClass;

/**
 * Utility class used for ORM operations
 */
final class ORM{
    
    /**
     * Return a new instance of the class passed as string with the params passed as array
     * @param string $className The class name of the new instance
     * @param array $obj The params to fill the class with
     * @return IModel The new instance of the object
    */
    public static function getNewInstance(string $className, array $obj): IModel {
        $reflectionClass = new ReflectionClass($className);
        try{
            $obj = $reflectionClass->newInstanceArgs($obj);
            if(is_null($obj)) throw new ORMException(ORMException::$FAILED_MAPPING_OBJECT);
                        
            return $obj;
        }catch(Exception $ex){
            throw new ORMException(ORMException::$GENERIC_EXCEPTION.$ex->getMessage());
        }
    }

}