<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\Reflection;

use AbstractRepo\Attributes;
use AbstractRepo\Exceptions;
use AbstractRepo\Plugins\Utils\ArrayUtils;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Utility class used for reflection operations
 * @TODO: Refactor, phpdocs, cleaning and optimize.
 */
final class ReflectionUtility
{
    /**
     * Returns the reflected property with the attributeAttributes\PrimaryKey
     *
     * @param string|ReflectionClass $class
     * @return ReflectionProperty
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    public static function getPrimaryKeyProperty(string|ReflectionClass $class): ReflectionProperty
    {
        $properties = ReflectionUtility::getPropertyWithAttribute($class, Attributes\PrimaryKey::class);

        if (count($properties) == 0) {
            throw new Exceptions\ReflectionException("No Attributes\Key property defined in $class");
        }

        return $properties[0];
    }

    /**
     * Returns the reflected properties with the attribute Attributes\ForeignKey
     *
     * @param string|ReflectionClass $class
     * @return array
     * @throws ReflectionException
     */
    public static function getForeignKeyProperties(string|ReflectionClass $class): array
    {
        return ReflectionUtility::getPropertyWithAttribute($class, Attributes\ForeignKey::class);
    }

    /**
     * Returns the reflected property with the attribute passed
     *
     * @param string|ReflectionClass $class
     * @param string $attributeClass
     * @return array
     * @throws ReflectionException
     */
    public static function getPropertyWithAttribute(string|ReflectionClass $class, string $attributeClass): array
    {

        $properties = [];

        if (is_string($class)) {
            $class = new ReflectionClass($class);
        }

        $reflectionProperties = $class->getProperties();

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
    public static function getProperty(string $modelClass, string $propertyName): ReflectionProperty
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
     * @param ReflectionClass|ReflectionMethod|ReflectionProperty $reflectionObj
     * @param string $attributeClass
     * @return ReflectionAttribute|null
     */
    public static function getAttribute(ReflectionClass|ReflectionMethod|ReflectionProperty $reflectionObj, string $attributeClass): ReflectionAttribute|null
    {
        // Attributes of the property
        $attributes = $reflectionObj->getAttributes();

        if (count($attributes) == 0) return null;

        foreach ($attributes as $attribute) {
            $attributeName = $attribute->getName();

            if ($attributeName == $attributeClass) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Invoke the method of the class given.
     *
     * @param string $class
     * @param string $methodName
     * @param object|null $obj
     * @return mixed
     * @throws ReflectionException
     */
    public static function invokeMethodOfClass(string $class, string $methodName, ?object $obj): mixed
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
    public static function getClassShortName(string $class): string
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
    public static function class_implements(string $className, string $interfaceName): bool
    {
        foreach (class_implements($className) as $interface) {
            if ($interface == $interfaceName) {
                return true;
            }
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
    public static function getReflectionClass(string $class): ReflectionClass
    {
        return new ReflectionClass($class);
    }

    /**
     * Returns the table name of a model
     *
     * @param ReflectionClass $reflectionClass
     * @return mixed
     * @throws Exceptions\RepositoryException
     */
    public static function getTableName(ReflectionClass $reflectionClass): string
    {
        /**
         * Check if the model handled has the Attributes\Entity attribute
         */
        $reflectionEntityProperty = ReflectionUtility::getAttribute($reflectionClass, Attributes\Entity::class);

        // If there is no Attributes\Entity attribute it will trigger an Exceptions\RepositoryException
        if (!$reflectionEntityProperty) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_IS_NOT_ENTITY);
        }
        /**
         * @var Attributes\Entity $entityProperty
         */
        $entityProperty = $reflectionEntityProperty->newInstance();

        return $entityProperty->tableName;
    }

    /**
     * Returns the parameter requested from the constructor or null.
     *
     * @param ReflectionClass $reflectionClass
     * @param string $parameterName
     * @return ReflectionParameter|null
     */
    public static function getConstructorParameter(
        ReflectionClass $reflectionClass,
        string $parameterName
    ): ReflectionParameter|null
    {
        $constructorParams = $reflectionClass->getConstructor()->getParameters();
        return ArrayUtils::findFirstOrNull(
            fn(ReflectionParameter $param) => $param->getName() === $parameterName,
            $constructorParams
        );
    }
}