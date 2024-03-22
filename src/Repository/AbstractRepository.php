<?php

declare(strict_types=1);

namespace AbstractRepo\Repository;

use AbstractRepo\Attributes;
use AbstractRepo\Enums;
use AbstractRepo\Exceptions;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Interfaces;
use AbstractRepo\Interfaces\IModel;
use AbstractRepo\Models;
use AbstractRepo\Plugins\ORM\ORM;
use AbstractRepo\Plugins\PDO\PDOUtil;
use AbstractRepo\Plugins\QueryBuilder\QueryBuilder;
use AbstractRepo\Plugins\Reflection\ReflectionUtility;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
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
     * @var Models\ModelHandler The models handler
     */
    private Models\ModelHandler $modelHandler;

    /**
     * @param PDO $pdo
     * @throws ReflectionException
     * @throws RepositoryException
     */
    function __construct(
        protected PDO $pdo
    )
    {
        // If the repository doesn't implement IRepository it won't have the getModel method
        if (!$this instanceof Interfaces\IRepository) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::REPOSITORY_MUST_IMPLEMENTS);
        }

        // Invoke the method to get the model handled by the repository (ex: Book)
        $this->modelClass = ReflectionUtility::invokeMethodOfClass(get_class($this), "getModel", null);

        $modelReflectionClass = ReflectionUtility::getReflectionClass($this->modelClass);

        // Check if the class implements Interfaces\IModel
        if (!ReflectionUtility::class_implements($this->modelClass, Interfaces\IModel::class)) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_MUST_IMPLEMENTS);
        }

        $this->tableName = ReflectionUtility::getTableName($modelReflectionClass);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->modelHandler = new Models\ModelHandler();

        // Process the model
        $this->processModel($modelReflectionClass);
    }

    #region Private methods

    /**
     * Analyze the model class
     *
     * @param ReflectionClass $reflectionClass
     * @return void
     * @throws ReflectionException
     */
    private function processModel(ReflectionClass $reflectionClass): void
    {
        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            // Flag to see if a property is identity, so it doesn't need to be inserted
            $isIdentity = false;

            // Flag to check if is required
            $isRequired = false;

            // Flag to check if is fk and which type
            $typeOfFk = null;

            // If the column name of the fk is specified we store it
            $fkColumnName = null;

            // Attributes of the property
            $attributes = $reflectionProperty->getAttributes();

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $attributeName = $attribute->getName();

                // If is an identity we are not going to add it in the insert query
                if ($attributeName === Attributes\Key::class) {
                    $isIdentity = ReflectionUtility::invokeMethodOfClass(
                        get_class($attributeInstance),
                        Attributes\Key::isIdentityMethod,
                        $attributeInstance
                    );
                }

                // If it doesn't have a default value and is not a key identity then it's required
                $isRequired = !$reflectionProperty->hasDefaultValue() && !$isIdentity;

                // We get the ENUM type of Enums/Relationship if it is a foreign key and the column name
                if ($attributeName === Attributes\ForeignKey::class) {
                    $typeOfFk = ReflectionUtility::invokeMethodOfClass(
                        get_class($attributeInstance),
                        Attributes\ForeignKey::getRelationshipMethod,
                        $attributeInstance
                    );

                    $fkColumnName = ReflectionUtility::invokeMethodOfClass(
                        get_class($attributeInstance),
                        Attributes\ForeignKey::getColumnNameMethod,
                        $attributeInstance
                    );
                }
            }

            // Get property name and type
            $propertyName = $reflectionProperty->getName();
            $propertyType = strval($reflectionProperty->getType());

            $this->modelHandler->save(
                fieldName: $propertyName,
                fieldInfo: new Models\FieldInfo(
                    fieldName: $propertyName,
                    fieldType: $propertyType,
                    isRequired: $isRequired,
                    isIdentity: $isIdentity,
                    isFk: $typeOfFk !== null,
                    defaultValue: $reflectionProperty->getDefaultValue(),
                    fkType: $typeOfFk,
                    fkColumnName: $fkColumnName
                )
            );
        }
    }

    /**
     * Validates the model sent in the request
     *
     * @param IModel $model
     * @return void
     * @throws RepositoryException
     */
    private function validateRequest(Interfaces\IModel $model): void
    {
        if (get_class($model) !== $this->modelClass) {
            throw new Exceptions\RepositoryException("The model is not handled by the repository.");
        }
    }

    /**
     * Returns the PDOStatement for the insert operation
     *
     * @param IModel $model
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws RepositoryException If the model has no valid fields to take the data from
     */
    private function getInsertStatement(Interfaces\IModel $model): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);

        if (count($values) == 0) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::NO_MODEL_DATA_FOUND);
        }

        // Get an array with just the columns names
        $columns = array_map(fn(Models\ModelField $val) => $val->fieldName, $values);

        $queryBuilder->insert($this->tableName, $columns);

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        // For each placeholder (:colName) bind the param with its type
        $stmt = $this->bindValues($values, $stmt);

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the update operation
     *
     * @param IModel $model
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws RepositoryException If the model has no valid fields to take the data from
     */
    private function getUpdateStatement(Interfaces\IModel $model): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);

        if (count($values) == 0) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::NO_MODEL_DATA_FOUND);
        }

        // Get the update string (ex: col1 = :col1, col2 = :col2)
        $columns = array_map(fn(Models\ModelField $val) => $val->fieldName, $values);

        $queryBuilder->update($this->tableName, $columns);

        $keyProp = ReflectionUtility::getKeyProperty($model::class);
        $keyPropName = $keyProp->name;
        $keyPropValue = $keyProp->getValue($model);

        $queryBuilder->where("{$keyPropName} = {$keyPropValue}");

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        // Bind values to the statement
        $stmt = $this->bindValues($values, $stmt);

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the delete operation
     *
     * @param $id
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    private function getDeleteStatement($id): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        $keyProp = ReflectionUtility::getKeyProperty($this->modelClass);
        $keyPropName = $keyProp->name;
        $keyPropValue = $id;

        $queryBuilder
            ->delete($this->tableName)
            ->where("{$keyPropName} = {$keyPropValue}");

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        return $stmt;
    }

    /**
     * Returns an array used in the insert an update operation to get every value of the object
     *
     * @param Interfaces\IModel $model
     * @return array
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    private function getValuesFromModel(Interfaces\IModel $model): array
    {
        $values = [];

        foreach ($model as $propertyName => $value) {
            $field = $this->modelHandler->get($propertyName);

            $propertyType = $field->fieldType;

            // If it's not an identity
            if (!$field->isIdentity) {

                // If is a fk
                if ($field->fkType) {

                    if ($field->fkType == Enums\Relationship::MANY_TO_ONE || $field->fkType == Enums\Relationship::ONE_TO_ONE) {
                        // It takes the value of the fk from the model
                        $fkKeyPropertyRefl = ReflectionUtility::getKeyProperty($field->fieldType);
                        $fkKeyProperty = $fkKeyPropertyRefl->name;
                        $value = $model->$propertyName->$fkKeyProperty;

                        // Check if the ID is valid and therefore if there is a related record in the database
                        $fkObj = $this->findById($value, $field->fieldType, $propertyName);

                        if (!$fkObj) {
                            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);
                        }

                        $propertyType = strval($fkKeyPropertyRefl->getType());

                        // If the column name is specified in the Attributes\ForeignKey attribute use it
                        if (!is_null($field->fkColumnName)) {
                            $propertyName = $field->fkColumnName;
                        }
                    }

                } else {
                    // If it's not a fk just add the value
                    $value = $value ?? $field->defaultValue ?? null;
                }

                if ($field->isRequired && empty($value)) {
                    throw new Exceptions\RepositoryException("{$propertyName} is required!");
                }

                if (empty($value)) {
                    continue;
                }

                // Array to store all the information to create the insert
                $values[] = new Models\ModelField(
                    fieldName: $propertyName,
                    fieldType: $propertyType,
                    fieldValue: $value
                );
            }
        }
        return $values;
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
        $queryBuilder = (new QueryBuilder())
            ->select()
            ->from($tableName);

        // Get the Attributes\Key property of the model
        $property = ReflectionUtility::getProperty($modelClass, $property);

        // Get the name of the Attributes\Key
        $propertyName = $property->getName();

        // Get the PDO type of the property
        $idType = PDOUtil::getPDOType(strval($property->getType()));

        $queryBuilder->where("{$propertyName} = " . QueryBuilder::BIND_CHAR . "{$propertyName}");

        // Prepares, binds, executes and fetch the query
        $stmt = $this->pdo->prepare($queryBuilder->getQuery());
        $stmt->bindParam(QueryBuilder::BIND_CHAR . $propertyName, $value, $idType);
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);

        $mappedArr = [];

        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item, $modelClass);
        }

        return $mappedArr;
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
    private function getMappedObject(mixed $obj, string $modelClass): ?Interfaces\IModel
    {
        if (!isset($obj) || !$obj) return null;

        $fkProperties = ReflectionUtility::getFkProperties(modelClass: $modelClass);

        foreach ($fkProperties as $fkProperty) {
            $fkField = $this->modelHandler->get(fieldName: $fkProperty->name);

            $columnName = $fkField->fkColumnName;

            $fkReflClass = ReflectionUtility::getReflectionClass(class: $fkField->fieldType);

            $fkTableName = ReflectionUtility::getTableName(reflectionClass: $fkReflClass);

            // Removes the id from the object
            $id = $obj[$columnName];
            unset($obj[$columnName]);

            $fkObj = $this->findById(
                id: $id,
                class: $fkField->fieldType,
                table: $fkTableName
            );

            if (is_null($fkObj)) {
                throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);
            }

            $obj[$fkField->fieldName] = $fkObj;
        }

        try {
            $mappedObj = ORM::getNewInstance($modelClass, (array)$obj);
        } catch (Exceptions\ORMException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }

        return $mappedObj;
    }

    /**
     * Bind the values in the array passed to the statement received
     *
     * @param Models\ModelField[] $values
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    private function bindValues(array $values, PDOStatement $stmt): PDOStatement
    {
        foreach ($values as $val) {
            $type = PDOUtil::getPDOType($val->fieldType);

            $placeholder = QueryBuilder::BIND_CHAR . $val->fieldName;
            $value = $val->fieldValue;

            $stmt->bindValue($placeholder, $value, $type);
        }
        return $stmt;
    }

    /**
     * Returns the total amount of items of a given model
     *
     * @param string $tableName
     * @return int
     */
    private function getItemsCount(string $tableName): int
    {
        $query = (new QueryBuilder())
            ->select(["COUNT(*) as itemsCount"])
            ->from($tableName)
            ->getQuery();

        $stmt = $this->pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_CLASS);

        if (!$result) {
            return 0;
        }

        return $result[0]->itemsCount;
    }

    #endregion

    #region Public methods

    /**
     * Entry function to findAll models
     *
     * @param int|null $page
     * @param int|null $itemsPerPage
     * @return Models\FetchedData|IModel[]
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws RepositoryException
     */
    public function findAll(?int $page = null, ?int $itemsPerPage = null): Models\FetchedData|array
    {
        $isPaginated = $page !== null && $itemsPerPage !== null;

        $queryBuilder = (new QueryBuilder())
            ->select()
            ->from($this->tableName);

        if ($isPaginated) {
            $queryBuilder->paginate($page, $itemsPerPage);
        }

        $stmt = $this->pdo->query($queryBuilder->getQuery());
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);
        $mappedArr = [];

        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item, $this->modelClass);
        }

        if ($isPaginated) {
            $itemsCount = $this->getItemsCount($this->tableName);
            $totalPages = (int)round($itemsCount / $itemsPerPage);

            return new Models\FetchedData(
                data: $mappedArr,
                currentPage: $page,
                itemsPerPage: $itemsPerPage,
                totalPages: $totalPages
            );
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
        $keyProperty = ReflectionUtility::getKeyProperty($modelClass);

        // Get the name of the Attributes\Key
        $propertyName = $keyProperty->getName();

        $res = $this->findWhere($tableName, $modelClass, $propertyName, $id);

        if (count($res) == 0) return null;

        if (count($res) > 1) throw new Exceptions\RepositoryException(Exceptions\RepositoryException::FETCH_BY_ID_MULTIPLE_RESULTS);

        return $res[0];
    }

    /**
     * Entry function to save a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the database triggers an exception
     * @throws ReflectionException
     */
    public function save(Interfaces\IModel $model): void
    {
        try {
            $this->validateRequest($model);

            $stmt = $this->getInsertStatement($model);
            $stmt->execute();

            // Set id to the saved model
            ReflectionUtility::getKeyProperty($this->modelClass)->setValue($model, $this->pdo->lastInsertId());
        } catch (PDOException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to update a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException If the database triggers an exception
     * @throws ReflectionException
     */
    public function update(Interfaces\IModel $model): void
    {
        try {
            $this->validateRequest($model);

            $stmt = $this->getUpdateStatement($model);
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
            $stmt = $this->getDeleteStatement($id);
            $stmt->execute();
        } catch (PDOException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }
    #endregion
}