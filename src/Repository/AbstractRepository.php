<?php

declare(strict_types=1);

namespace AbstractRepo\Repository;

use AbstractRepo\Attributes;
use AbstractRepo\Enums;
use AbstractRepo\Exceptions;
use AbstractRepo\Interfaces;
use AbstractRepo\Util;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionException;

/**
 * The abstract class from which extends the repository
 */
abstract class AbstractRepository
{
    /**
     * @var string|mixed The class of the model handled by the repository (ex: AbstractRepo\Models\Book)
     */
    private string $modelClass;

    /**
     * @var string|mixed The name of the table in which the model is stored
     */
    private string $tableName;

    /**
     * @param PDO $pdo The pdo object to use the database
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    function __construct(
        protected PDO  $pdo
    )
    {
        // If the repository doesn't implements IRepository it won't have the getModel method
        if (!$this instanceof Interfaces\IRepository) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::REPOSITORY_MUST_IMPLEMENTS);

        // Invoke the method to get the model handled by the repository (ex: Book)
        $this->modelClass = Util\ReflectionUtility::invokeMethodOfClass(get_class($this), "getModel", null);

        // Check if the class implements Interfaces\IModel
        if (!Util\ReflectionUtility::class_implements($this->modelClass, Interfaces\IModel::class))
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_MUST_IMPLEMENTS);

        // Check if the model handled has the Attributes\Entity attribute
        $entityProperty = Util\ReflectionUtility::getAttribute(Util\ReflectionUtility::getReflectionClass($this->modelClass), Attributes\Entity::class);

        // If there is not Attributes\Entity attribute it will trigger a Exceptions\RepositoryException
        if (is_null($entityProperty)) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_IS_NOT_ENTITY);

        // If there is no table name specified in the constructor of the Attributes\Entity attribute 
        // it will take the name of the model class
        if (count($entityProperty->getArguments()) == 0) {
            $this->tableName = strtolower(Util\ReflectionUtility::getClassShortName($this->modelClass));
        } else {
            $this->tableName = $entityProperty->getArguments()[0];
        }

        // Assign the pdo object
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    #region Public methods

    /**
     * Entry function to findAll models
     *
     * @return array|null
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    public function findAll(): ?array
    {

        $query = "SELECT * FROM {$this->tableName}";
        $stmt = $this->pdo->query($query);
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);
        $mappedArr = [];

        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item, $this->modelClass);
        }

        return $mappedArr;
    }


    /**
     * Entry function to find by id a Model
     * @param $id
     * @param string|null $class
     * @param string|null $table
     * @return Interfaces\IModel|null
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If it finds multiple results, meaning database or entities are not configured properly
     * @throws ReflectionException
     */
    public function findById($id, string $class = null, string $table = null): ?Interfaces\IModel
    {
        // Since this function will be called recursively to handle nesting and foreign Attributes\Keys, it could be
        // called with different modelClasses and tableNames
        $modelClass = $class ?? $this->modelClass;
        $tableName = $table ?? $this->tableName;

        // Get the Attributes\Key property of the model
        $keyProperty = Util\ReflectionUtility::getKeyProperty($modelClass);

        // Get the name of the Attributes\Key
        $propertyName = $keyProperty->getName();

        $res = $this->findWhere($tableName, $modelClass, $propertyName, $id);

        if (count($res) == 0) return null;

        if (count($res) > 1) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::FETCH_BY_ID_MULTIPLE_RESULTS);

        return $res[0];
    }

    /**
     * Make a filtered select with the parameter passed
     *
     * @param string $tableName
     * @param string $modelClass
     * @param string $property
     * @param $value
     * @return array|null
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    private function findWhere(string $tableName, string $modelClass, string $property, $value): ?array
    {

        $query = "SELECT * FROM {$tableName} WHERE ";
        // Get the Attributes\Key property of the model
        $property = Util\ReflectionUtility::getProperty($modelClass, $property);

        // Get the name of the Attributes\Key
        $propertyName = $property->getName();

        // Get the PDO type of the property
        $idType = $this->getPDOType(strval($property->getType()));

        // Creates the where condition (ex: WHERE ID = :ID)
        $placeholder = ":$propertyName";
        $query .= "$propertyName = $placeholder";

        // Prepares, binds, executes and fetch the query
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam($placeholder, $value, $idType);
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);

        $mappedArr = [];

        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item, $modelClass);
        }

        return $mappedArr;
    }

    /**
     * Entry function to save a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\EnumException
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the database triggers an exception
     * @throws ReflectionException
     */
    public function save(Interfaces\IModel $model): void
    {
        try {
            $stmt = $this->getInsertStatement($model, $this->pdo);
            $stmt->execute();
        } catch (PDOException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to update a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\EnumException
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the database triggers an exception
     * @throws ReflectionException
     */
    public function update(Interfaces\IModel $model): void
    {
        try {
            $stmt = $this->getUpdateStatement($model, $this->pdo);
            $stmt->execute();
        } catch (PDOException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to delete a Model
     *
     * @param $id
     * @return void
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    public function delete($id): void
    {
        try {
            $stmt = $this->getDeleteStatement($id, $this->pdo);
            $stmt->execute();
        } catch (PDOException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Returns the instance model from the array gave by the database
     * It handles the foreign Attributes\Keys with the
     *
     * @param mixed $obj
     * @param string $modelClass
     * @return Interfaces\IModel|null
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the related object is not found or if the orm mapping triggers an exception
     * @throws ReflectionException
     */
    public function getMappedObject(mixed $obj, string $modelClass): ?Interfaces\IModel
    {
        if (!isset($obj) || !$obj) return null;

        $fkProperties = Util\ReflectionUtility::getFkProperties($modelClass);

        foreach ($fkProperties as $fkProperty) {
            $columnName = null;
            $fkAttribute = Util\ReflectionUtility::getAttribute($fkProperty, Attributes\ForeignKey::class);
            $arguments = $fkAttribute->getArguments();

            // If the column name is specified in the Attributes\ForeignKey attribute use it
            if (count($arguments) < 2) {
                $columnName = strtolower($fkProperty->name);
            } else {
                $columnName = $arguments[1];
            }
            // Removes the id from the object
            $id = $obj[$columnName . "_id"];
            unset($obj[$columnName . "_id"]);

            // Needs the full path of the class of the model (ex: AbstractRepo\Model\User)
            $reflClass = Util\ReflectionUtility::getReflectionClass($modelClass);
            $prop = $reflClass->getProperty($fkProperty->name);
            $fkClass = $prop->getType()->getName();

            $fkObj = $this->findById($id, $fkClass, $columnName);

            if (is_null($fkObj)) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);

            $obj[$fkProperty->name] = $fkObj;
        }

        try {
            $mappedObj = Util\ORM::getNewInstance($modelClass, (array)$obj);
        } catch (Exceptions\ORMException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }

        return $mappedObj;
    }

    #endregion

    #region Private methods

    /**
     * Returns the PDOStatement for the insert operation
     *
     * @param Interfaces\IModel $model
     * @param PDO $pdo
     * @return PDOStatement
     * @throws Exceptions\EnumException
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the model has no valid fields to take the data from
     * @throws ReflectionException
     */
    private function getInsertStatement(Interfaces\IModel $model, PDO $pdo): PDOStatement
    {
        $query = "INSERT INTO $this->tableName";

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);

        if (count($values) == 0) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::NO_MODEL_DATA_FOUND);
        }

        // Get an array with just the columns names
        $columns = array_map(
            function (Util\ModelField $val) {
                return $val->fieldName;
            },
            $values
        );

        // Creates the second part of the query (ex: (col1,col2) VALUES ( :col1,:col2))
        $query .= " ( " . implode(",", $columns) . " ) VALUES (" . implode(",", array_map(function ($val) {
                return ":$val";
            }, $columns)) . ");";

        $stmt = $pdo->prepare($query);

        // For each placeholder (:colName) bind the param with its type
        $stmt = $this->bindValues($values, $stmt);

        return $stmt;
    }

    /**
     * Returns an array used in the insert an update operation to get every value of the object
     *
     * @param Interfaces\IModel $model
     * @return array
     * @throws Exceptions\EnumException
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    private function getValuesFromModel(Interfaces\IModel $model): array
    {
        $classModel = get_class($model);

        $reflectionClass = Util\ReflectionUtility::getReflectionClass($classModel);

        $reflectionProperties = $reflectionClass->getProperties();

        $values = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            // Flag to see if a property is identity so it doesn't need to be inserted
            $isIdentity = false;

            // Flag to check if is required
            $isRequired = false;

            // Flag to check if is fk and which type
            $typeOfFk = null;

            // If the column name of the fk is specified we store it
            $fkColumnName = null;

            // Attributes of the property
            $attributes = $reflectionProperty->getAttributes();

            // If the property has no attributes it will be skipped
            if (count($attributes) == 0) continue;

            foreach ($attributes as $attribute) {
                $attributeName = $attribute->getName();

                // If is an identity we are not going to add it in the insert query
                if ($attributeName == Attributes\Key::class && isset($attribute->getArguments()[0])) {
                    $isIdentity = true;
                }

                // If is a Attributes\Key not identity or if is required we must insert it
                if (($attributeName == Attributes\Key::class && !isset($attribute->getArguments()[0]))
                    || $attributeName == Attributes\Required::class
                ) {
                    $isRequired = true;
                }

                // We get the ENUM type of Enums/Relationship if it is a foreign Attributes\Key and the eventual column name
                if ($attributeName == Attributes\ForeignKey::class) {
                    $arguments = $attribute->getArguments();
                    $typeOfFk = Enums\Relationship::fromString($arguments[0]->name);
                    if (count($arguments) > 1) {
                        $fkColumnName = $arguments[1];
                    }
                }
            }

            // Get property name and type
            $propertyName = $reflectionProperty->getName();
            $propertyType = strval($reflectionProperty->getType());

            // If it's not an identity
            if (!$isIdentity) {

                // If is a fk
                if ($typeOfFk != null) {

                    // Handle the MANY_TO_ONE relation
                    if ($typeOfFk == Enums\Relationship::MANY_TO_ONE || $typeOfFk == Enums\Relationship::ONE_TO_ONE) {
                        // It takes the value of the fk from the model
                        $fkKeyPropertyRefl = Util\ReflectionUtility::getKeyProperty($propertyType);
                        $fkKeyProperty = $fkKeyPropertyRefl->name;
                        $value = $model->$propertyName->$fkKeyProperty;

                        // Check if the ID is valid and therefore if there is a related record in the database                                             
                        $fkObj = $this->findById($value, $propertyType, $propertyName);

                        if (is_null($fkObj)) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);

                        $propertyType = strval($fkKeyPropertyRefl->getType());

                        // If the column name is specified in the Attributes\ForeignKey attribute use it
                        if (!is_null($fkColumnName)) {
                            $propertyName = $fkColumnName;
                        }

                        // Get the fk column
                        $propertyName .= "_id";
                    }

                } else {
                    // If it's not a fk just add the value
                    $value = $reflectionProperty->getValue($model);
                }

                if ($isRequired && empty($value)) {
                    throw new Exceptions\RepositoryException("$propertyName is required!");
                }

                if (empty($value)) {
                    continue;
                }

                // Array to store all the information to create the insert
                $values[] = new Util\ModelField($propertyName, $propertyType, $value);
            }
        }

        return $values;
    }

    /**
     * Returns the PDOStatement for the update operation
     *
     * @param Interfaces\IModel $model
     * @param PDO $pdo
     * @return PDOStatement
     * @throws Exceptions\EnumException
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the model has no valid fields to take the data from
     * @throws ReflectionException
     */
    private function getUpdateStatement(Interfaces\IModel $model, PDO $pdo): PDOStatement
    {

        $query = "UPDATE {$this->tableName} SET ";

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);

        if (count($values) == 0) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::NO_MODEL_DATA_FOUND);
        }

        // Get the update string (ex: col1 = :col1, col2 = :col2)
        $columns = array_map(
            function (Util\ModelField $val) {
                return $val->fieldName . " = :" . $val->fieldName;
            },
            $values
        );

        $query .= implode(",", $columns);

        $keyProp = Util\ReflectionUtility::getKeyProperty($model::class);
        $keyPropName = $keyProp->name;
        $keyPropValue = $keyProp->getValue($model);

        $query .= " WHERE $keyPropName = $keyPropValue";

        $stmt = $pdo->prepare($query);

        // Bind values to the statement
        $stmt = $this->bindValues($values, $stmt);

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the delete operation
     *
     * @param $id
     * @param PDO $pdo
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    private function getDeleteStatement($id, PDO $pdo): PDOStatement
    {

        $query = "DELETE FROM {$this->tableName} WHERE ";

        $keyProp = Util\ReflectionUtility::getKeyProperty($this->modelClass);
        $keyPropName = $keyProp->name;
        $keyPropValue = $id;

        $query .= " $keyPropName = $keyPropValue";

        $stmt = $pdo->prepare($query);

        return $stmt;
    }

    /**
     * Bind the values in the array passed to the statement received
     *
     * @param Util\ModelField[] $values
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    private function bindValues(array $values, PDOStatement $stmt): PDOStatement
    {
        foreach ($values as $val) {

            $type = $this->getPDOType($val->fieldType);

            $placeholder = ":" . $val->fieldName;
            $value = $val->fieldValue;

            $stmt->bindValue($placeholder, $value, $type);
        }
        return $stmt;
    }

    /**
     * Returns the PDO type from the PHP type
     *
     * @param string $type
     * @return ?int
     */
    private function getPDOType(string $type): ?int
    {
        return match ($type) {
            'int', '?int' => PDO::PARAM_INT,
            '?string', 'string' => PDO::PARAM_STR,
            default => null,
        };
    }
    #endregion
}