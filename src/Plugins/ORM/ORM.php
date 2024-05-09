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
 */
final class ORM
{

    /**
     * Return a new instance of the class passed as string with the params passed as array
     *
     * @param string $className The class name of the new instance
     * @param array $object The params to fill the class with
     * @return Interfaces\IModel The new instance of the object
     * @throws Exceptions\ORMException
     * @throws ReflectionException
     */
    public static function getNewInstance(string $className, array $object): Interfaces\IModel
    {
        $reflectionClass = new ReflectionClass($className);
        try {
            $object = $reflectionClass->newInstanceArgs($object);

            // @codeCoverageIgnoreStart
            if (is_null($object)) {
                throw new Exceptions\ORMException(Exceptions\ORMException::FAILED_MAPPING_OBJECT);
            }
            // @codeCoverageIgnoreEnd

            return $object;
        } catch (Exception $ex) {
            throw new Exceptions\ORMException(Exceptions\ORMException::GENERIC_EXCEPTION . $ex->getMessage());
        }
    }
}