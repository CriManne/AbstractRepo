<?php

declare(strict_types=1);

namespace AbstractRepo\Util;

use AbstractRepo\Attributes;
use AbstractRepo\Exceptions;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Utility class used for reflection operations
 */
final class ReflectionUtility
{
    /**
     * Returns the reflected property with the attributeAttributes\Key
     *
     * @param string $modelClass
     * @return ReflectionProperty
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    static public function getKeyProperty(string $modelClass): ReflectionProperty
    {
        $properties = ReflectionUtility::getPropertyWithAttribute($modelClass, Attributes\Key::class);

        if (count($properties) == 0) throw new Exceptions\ReflectionException("NoAttributes\Key property defined in $modelClass");

        return $properties[0];
    }

    /**
     * Returns the reflected properties with the attributeAttributes\Key
     *
     * @param string $modelClass
     * @return array
     * @throws Exceptions\NotImplementedException
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    static public function getKeysProperties(string $modelClass): array
    {
        $properties = ReflectionUtility::getPropertyWithAttribute($modelClass, Attributes\Key::class);

        if (count($properties) == 0) throw new Exceptions\ReflectionException("NoAttributes\Key property defined in $modelClass");

        throw new Exceptions\NotImplementedException();
    }

    /**
     * Returns the reflected properties with the attribute Attributes\ForeignKey
     *
     * @param string $modelClass
     * @return array
     * @throws ReflectionException
     */
    static public function getFkProperties(string $modelClass): array
    {
        $properties = ReflectionUtility::getPropertyWithAttribute($modelClass, Attributes\ForeignKey::class);
        return $properties;
    }

    /**
     * Returns the reflected property with the attribute passed
     *
     * @param string $modelClass
     * @param string $attributeClass
     * @return array
     * @throws ReflectionException
     */
    static public function getPropertyWithAttribute(string $modelClass, string $attributeClass): array
    {

        $properties = [];

        $reflectionClass = new ReflectionClass($modelClass);

        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {

            $attr = ReflectionUtility::getAttribute($reflectionProperty, $attributeClass);

            if ($attr != null) $properties[] = $reflectionProperty;
        }

        return $properties;
    }

    /**
     * Returns the reflected property with the name passed
     *
     * @param string $modelClass
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    static public function getProperty(string $modelClass, string $propertyName): ReflectionProperty
    {
        $reflectionClass = new ReflectionClass($modelClass);

        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {

            if ($reflectionProperty->name == $propertyName) return $reflectionProperty;

        }

        throw new Exceptions\ReflectionException(Exceptions\ReflectionException::PROPERTY_NOT_FOUND);
    }

    /**
     * Returns the reflected property with the attribute passed
     *
     * @param ReflectionClass|ReflectionMethod|ReflectionProperty $reflectedObj
     * @param string $propertyClass
     * @return ReflectionAttribute|null
     */
    static public function getAttribute(ReflectionClass|ReflectionMethod|ReflectionProperty $reflectedObj, string $propertyClass): ReflectionAttribute|null
    {
        // Attributes of the property
        $attributes = $reflectedObj->getAttributes();

        if (count($attributes) == 0) return null;

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();

            if ($attributeName == $propertyClass) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Runs the method of the class passed
     *
     * @param string $class
     * @param string $methodName
     * @param object|null $obj
     * @return mixed
     * @throws ReflectionException
     */
    static public function invokeMethodOfClass(string $class, string $methodName, ?object $obj): mixed
    {
        // Get reflection method getModel
        $method = new ReflectionMethod($class, $methodName);

        return $method->invoke($obj);
    }

    /**
     * Returns the short name of a class
     *
     * @param string $class
     * @return string
     * @throws ReflectionException
     * @throws ReflectionException
     */
    static public function getClassShortName(string $class): string
    {
        $reflectedModel = new ReflectionClass($class);
        return $reflectedModel->getShortName();
    }

    /**
     * Override of the class_implements method to check if a class implements a specific interface
     *
     * @param string $className
     * @param string $interfaceName
     * @return boolean
     */
    static public function class_implements(string $className, string $interfaceName): bool
    {
        foreach (class_implements($className) as $interface) {
            if ($interface == $interfaceName) return true;
        }

        return false;
    }

    /**
     * Returns a new instance of reflection class of the class passed
     *
     * @param string $class
     * @return ReflectionClass
     * @throws ReflectionException
     * @throws ReflectionException
     */
    static public function getReflectionClass(string $class): ReflectionClass
    {
        return new ReflectionClass($class);
    }
}