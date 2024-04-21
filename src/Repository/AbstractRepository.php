<?php

declare(strict_types=1);

namespace AbstractRepo\Repository;

use AbstractRepo\Attributes;
use AbstractRepo\DataModels\FetchedData;
use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\DataModels\FieldInfo;
use AbstractRepo\DataModels\ModelField;
use AbstractRepo\Enums;
use AbstractRepo\Exceptions;
use AbstractRepo\Interfaces;
use AbstractRepo\Interfaces\IModel;
use AbstractRepo\Plugins\ModelHandler\ModelHandler;
use AbstractRepo\Plugins\ORM\ORM;
use AbstractRepo\Plugins\PDO\PDOUtil;
use AbstractRepo\Plugins\QueryBuilder\QueryBuilder;
use AbstractRepo\Plugins\Reflection\ReflectionUtility;
use Exception;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

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
     * @var ModelHandler The models handler
     */
    private ModelHandler $modelHandler;

    /**
     * @param PDO $pdo
     * @throws Exceptions\RepositoryException
     */
    function __construct(
        protected PDO $pdo
    )
    {
        try {
            // If the repository doesn't implement IRepository it won't have the getModel method
            if (!$this instanceof Interfaces\IRepository) {
                throw new Exceptions\RepositoryException(Exceptions\RepositoryException::REPOSITORY_MUST_IMPLEMENTS);
            }

            // Invoke the method to get the model handled by the repository (ex: Book)
            $this->modelClass = $this->getModel();

            $modelReflectionClass = ReflectionUtility::getReflectionClass($this->modelClass);

            // Check if the class implements Interfaces\IModel
            if (!ReflectionUtility::class_implements($this->modelClass, Interfaces\IModel::class)) {
                throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_MUST_IMPLEMENTS);
            }

            $this->tableName = ReflectionUtility::getTableName($modelReflectionClass);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->modelHandler = new ModelHandler();

            // Process the model
            $this->processModel($modelReflectionClass);
        } catch (Exception $e) {
            throw new Exceptions\RepositoryException($e->getMessage());
        }
    }

    #region Private methods

    /**
     * Analyze the model class
     *
     * @param ReflectionClass $reflectionClass
     * @return void
     * @throws ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws Exceptions\ReflectionException
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

            /**
             * The name of the reference column by the fk
             */
            $fkColumnName = null;

            /**
             * The type of the reference column by the fk
             */
            $fkColumnType = null;

            // If the property is searchable
            $isSearchable = false;

            // If the property is key
            $isKey = false;

            // Attributes of the property
            $attributes = $reflectionProperty->getAttributes();

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $attributeName = $attribute->getName();

                // If the property is searchable
                if ($attributeName === Attributes\Searchable::class) {
                    $isSearchable = true;
                }

                /**
                 * @var Attributes\Key $attributeInstance
                 */
                if ($attributeName === Attributes\Key::class) {
                    $isKey = true;
                    $isIdentity = $attributeInstance->autoIncrement;
                }

                /**
                 * @var Attributes\ForeignKey $attributeInstance
                 */
                if ($attributeName === Attributes\ForeignKey::class) {
                    $typeOfFk = $attributeInstance->relationship;

                    $fkColumnName = $attributeInstance->columnName;

                    $keyProperty = ReflectionUtility::getKeyProperty($reflectionProperty->getType()->getName());
                    $fkColumnType = $keyProperty->getType()->getName();
                }
            }

            /**
             * This check had to be made due to a bug/bad documentation of the method {@see ReflectionProperty::hasDefaultValue()}
             * that doesn't work with promoted properties.
             */
            if (!$reflectionProperty->isPromoted()) {
                // If it doesn't have a default value and is not a key identity then it's required
                $isRequired = !$reflectionProperty->hasDefaultValue() && !$isIdentity;
            } else {
                /**
                 * If it's a promoted property check the default value in the constructor by getting the reflection parameter
                 */
                $constructorParams = $reflectionClass->getConstructor()->getParameters();
                $foundParams = array_values(array_filter($constructorParams, fn(ReflectionParameter $param) => $param->getName() === $reflectionProperty->getName()));

                if (empty($foundParams) || count($foundParams) > 1) {
                    throw new Exceptions\RepositoryException(Exceptions\RepositoryException::INVALID_PROMOTED_PROPERTY);
                }

                $isRequired = !($foundParams[0]->isDefaultValueAvailable()) && !$isIdentity;
            }

            // Get property name and type
            $propertyName = $reflectionProperty->getName();
            $propertyType = $reflectionProperty->getType()->getName();

            $this->modelHandler->save(
                fieldName: $propertyName,
                fieldInfo: new FieldInfo(
                    fieldName: $propertyName,
                    fieldType: $propertyType,
                    isRequired: $isRequired,
                    isKey: $isKey,
                    isIdentity: $isIdentity,
                    isFk: $typeOfFk !== null,
                    defaultValue: $reflectionProperty->getDefaultValue(),
                    relationshipType: $typeOfFk,
                    fkColumnName: $fkColumnName,
                    fkColumnType: $fkColumnType
                )
            );

            if ($isSearchable) {
                if ($typeOfFk !== null) {
                    $this->modelHandler->addSearchableField($fkColumnName);
                } else {
                    $this->modelHandler->addSearchableField($propertyName);
                }
            }
        }
    }

    /**
     * Validates the model sent in the request
     *
     * @param IModel $model
     * @return void
     * @throws Exceptions\RepositoryException
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
     * @throws Exceptions\RepositoryException If the model has no valid fields to take the data from
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
        $columns = array_map(fn(ModelField $val) => $val->fieldName, $values);

        $queryBuilder->insert($this->tableName, $columns);

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        // For each placeholder (:colName) bind the param with its type
        return $this->bindValues($values, $stmt);
    }

    /**
     * Returns the PDOStatement for the update operation
     *
     * @param IModel $model
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws Exceptions\RepositoryException If the model has no valid fields to take the data from
     */
    private function getUpdateStatement(Interfaces\IModel $model): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);

        if (count($values) == 0) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::NO_MODEL_DATA_FOUND);
        }

        $keyProp = $this->modelHandler->getKey();
        $keyPropName = $keyProp->fieldName;
        $keyPropValue = $model->$keyPropName;

        // Get the update string (ex: col1 = :col1, col2 = :col2)
        $nonPkColumns = [];

        foreach ($values as $val) {
            if ($val->fieldName !== $keyPropName) {
                $nonPkColumns[] = $val->fieldName;
            }
        }

        $queryBuilder->update($this->tableName, $nonPkColumns);

        $queryBuilder->where("{$keyPropName} = " . QueryBuilder::BIND_CHAR . "{$keyPropName}");

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        // Bind values to the statement and also for the where id = :id
        $stmt = $this->bindValues($values, $stmt);

        $stmt->bindParam($keyPropName, $keyPropValue, PDOUtil::getPDOType(gettype($keyPropValue)));

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the delete operation
     *
     * @param $id
     * @return PDOStatement
     */
    private function getDeleteStatement($id): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        $keyProp = $this->modelHandler->getKey();

        // If it's a fk use the foreign key column name
        if ($keyProp->isFk) {
            $keyPropName = $keyProp->fkColumnName;
        } else {
            $keyPropName = $keyProp->fieldName;
        }

        $keyPropValue = $id;

        $queryBuilder
            ->delete($this->tableName)
            ->where("{$keyPropName} = " . QueryBuilder::BIND_CHAR . "{$keyPropName}");

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        $stmt->bindParam($keyPropName, $keyPropValue, PDOUtil::getPDOType(gettype($keyPropValue)));

        return $stmt;
    }

    /**
     * Returns an array used in the insert an update operation to get every value of the object
     *
     * @param Interfaces\IModel $model
     * @return ModelField[]
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
                if ($field->isFk) {

                    if ($field->relationshipType == Enums\Relationship::MANY_TO_ONE || $field->relationshipType == Enums\Relationship::ONE_TO_ONE) {
                        // It takes the value of the fk from the model
                        $fkKeyPropertyReflected = ReflectionUtility::getKeyProperty($field->fieldType);
                        $fkKeyProperty = $fkKeyPropertyReflected->name;
                        $value = $model->$propertyName->$fkKeyProperty;

                        // Take table name of the fk property
                        $fkReflectedClass = ReflectionUtility::getReflectionClass($field->fieldType);
                        $fkTableName = ReflectionUtility::getTableName($fkReflectedClass);

                        // Check if the ID is valid and therefore if there is a related record in the database
                        $fkObj = $this->findById($value, $field->fieldType, $fkTableName);

                        if (!$fkObj) {
                            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);
                        }

                        $propertyType = strval($fkKeyPropertyReflected->getType());

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
                $values[] = new ModelField(
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
     * @param mixed $value
     * @return array|null
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     * @throws Exceptions\ReflectionException
     */
    private function findWhere(string $tableName, string $modelClass, string $property, mixed $value): ?array
    {
        $queryBuilder = (new QueryBuilder())
            ->select()
            ->from($tableName);

        /**
         * If this is true {$modelClass !== $this->modelClass}, it means that the model passed is not the same as
         * the one handled by the repository. So we have to check on the other model
         */
        if ($modelClass !== $this->modelClass) {
            $property = ReflectionUtility::getProperty($modelClass, $property);

            $fkAttribute = ReflectionUtility::getAttribute($property, Attributes\ForeignKey::class);

            if ($fkAttribute !== null) {
                /**
                 * @var Attributes\ForeignKey $fkAttributeInstance
                 */
                $fkAttributeInstance = $fkAttribute->newInstance();

                $propertyName = $fkAttributeInstance->columnName;

                $keyProperty = ReflectionUtility::getKeyProperty($modelClass);
                $propertyType = PDOUtil::getPDOType($keyProperty->getType()->getName());
            } else {
                $propertyName = $property->getName();
                $propertyType = PDOUtil::getPDOType($property->getType()->getName());
            }
        } else {
            $property = $this->modelHandler->get($property);

            if ($property->isFk) {
                $propertyName = $property->fkColumnName;
                $propertyType = PDOUtil::getPDOType($property->fkColumnType);
            } else {
                $propertyName = $property->fieldName;
                $propertyType = PDOUtil::getPDOType($property->fieldType);
            }
        }

        $queryBuilder->where("{$propertyName} = " . QueryBuilder::BIND_CHAR . "{$propertyName}");

        // Prepares, binds, executes and fetch the query
        $stmt = $this->pdo->prepare($queryBuilder->getQuery());
        $stmt->bindParam(QueryBuilder::BIND_CHAR . $propertyName, $value, $propertyType);
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

            $fkReflectedClass = ReflectionUtility::getReflectionClass(class: $fkField->fieldType);

            $fkTableName = ReflectionUtility::getTableName(reflectionClass: $fkReflectedClass);

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
     * @param ModelField[] $values
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
     * Returns the total amount of items of a given query
     *
     * @param string $subquery
     * @param FetchParams|null $params
     * @return int
     */
    private function getItemsCount(string $subquery, ?FetchParams $params): int
    {
        $query = (new QueryBuilder())
            ->select(["COUNT(*) as itemsCount"])
            ->from("({$subquery}) AS subquery")
            ->getQuery();

        $stmt = $this->pdo->prepare($query);

        $this->bindParams($stmt, $params);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_CLASS);

        if (!$result) {
            return 0;
        }

        return $result[0]->itemsCount;
    }

    private function bindParams(PDOStatement $stmt, ?FetchParams $params): void
    {
        foreach ($params?->getBind() ?? [] as $prop => $value) {
            $type = gettype($value);
            if ($type === 'array') {
                $stringifiedArray = implode(',', $value);
                $stmt->bindParam($prop, $stringifiedArray);
            } else {
                $stmt->bindParam($prop, $value, PDOUtil::getPDOType(gettype($value)));
            }
        }
    }

    #endregion

    #region Public methods

    /**
     * Entry function to findAll models
     *
     * @param FetchParams|null $params
     * @return FetchedData|IModel[]
     * @throws Exceptions\RepositoryException
     */
    public function find(?FetchParams $params = null): FetchedData|array
    {
        try {
            $isPaginated = $params?->getPage() !== null && $params?->getItemsPerPage() !== null;

            $queryBuilder = (new QueryBuilder())
                ->select()
                ->from($this->tableName);

            if ($params?->getConditions()) {
                $queryBuilder->where($params->getConditions());
            }

            $queryNonPaginated = $queryBuilder->getQuery();

            if ($isPaginated) {
                $queryBuilder->paginate($params->getPage(), $params->getItemsPerPage());
            }

            $stmt = $this->pdo->prepare($queryBuilder->getQuery());

            $this->bindParams($stmt, $params);

            $stmt->execute();

            $arr = $stmt->fetchAll(PDO::FETCH_CLASS);
            $mappedArr = [];

            foreach ($arr as $item) {
                $mappedArr[] = $this->getMappedObject((array)$item, $this->modelClass);
            }

            if ($isPaginated) {
                $itemsCount = $this->getItemsCount($queryNonPaginated, $params);
                $totalPages = (int)ceil($itemsCount / $params->getItemsPerPage());

                return new FetchedData(
                    data: $mappedArr,
                    currentPage: $params->getPage(),
                    itemsPerPage: $params->getItemsPerPage(),
                    totalPages: $totalPages
                );
            }

            return $mappedArr;
        } catch (Exception $e) {
            throw new Exceptions\RepositoryException($e->getMessage());
        }
    }

    /**
     * @param mixed $query
     * @param int|null $page
     * @param int|null $itemsPerPage
     * @return FetchedData|array
     * @throws Exceptions\RepositoryException
     */
    public function findByQuery(mixed $query, ?int $page = null, ?int $itemsPerPage = null): FetchedData|array
    {
        try {
            $conditions = null;
            $bind = null;

            $searchableFields = $this->modelHandler->getSearchableFields();

            if (!empty($searchableFields)) {
                $query = '%' . $query . '%';

                $conditionsArray = [];
                $bind = [];

                foreach ($searchableFields as $field) {
                    $bindPlaceholder = "query{$field}";

                    $conditionsArray[] = "{$field} LIKE :{$bindPlaceholder}";
                    $bind[$bindPlaceholder] = $query;
                }

                $conditions = implode(' OR ', $conditionsArray);
            }

            return $this->find(
                new FetchParams(
                    page: $page,
                    itemsPerPage: $itemsPerPage,
                    conditions: $conditions,
                    bind: $bind
                )
            );
        } catch (Exception $e) {
            throw new Exceptions\RepositoryException($e->getMessage());
        }
    }

    /**
     * @param FetchParams|null $params
     * @return IModel|null
     * @throws Exceptions\RepositoryException
     */
    public function findFirst(?FetchParams $params = null): IModel|null
    {
        $params->setPage(0);
        $params->setItemsPerPage(1);
        $data = $this->find($params)->getData();
        if (empty($data)) {
            return null;
        }

        return $data[0];
    }

    /**
     * Entry function to find by id a Model
     * @param $id
     * @param string|null $class
     * @param string|null $table
     * @return IModel|null
     * @throws Exceptions\RepositoryException If it finds multiple results, meaning database or entities are not configured properly
     */
    public function findById($id, string $class = null, string $table = null): ?Interfaces\IModel
    {
        try {
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
        } catch (Exception $e) {
            throw new Exceptions\RepositoryException($e->getMessage());
        }
    }

    /**
     * Entry function to save a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\RepositoryException If the database triggers an exception
     */
    public function save(Interfaces\IModel $model): void
    {
        try {
            $this->validateRequest($model);

            $stmt = $this->getInsertStatement($model);
            $stmt->execute();

            // Set id to the saved model
            $key = ReflectionUtility::getKeyProperty($this->modelClass);

            $keyField = $this->modelHandler->getKey();

            if ($keyField->isIdentity) {
                $key->setValue($model, $this->pdo->lastInsertId());
            }
        } catch (Exception $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to update a Model
     *
     * @param Interfaces\IModel $model
     * @return void
     * @throws Exceptions\RepositoryException If the database triggers an exception
     */
    public function update(Interfaces\IModel $model): void
    {
        try {
            $this->validateRequest($model);

            $stmt = $this->getUpdateStatement($model);
            $stmt->execute();
        } catch (Exception $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to delete a Model
     *
     * @param $id
     * @return void
     * @throws Exceptions\RepositoryException
     */
    public function delete($id): void
    {
        try {
            $stmt = $this->getDeleteStatement($id);
            $stmt->execute();
        } catch (Exception $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }
    #endregion
}