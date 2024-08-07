<?php

declare(strict_types=1);

namespace AbstractRepo\Repository;

use AbstractRepo\Attributes;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\DataModels\FetchedData;
use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\DataModels\FieldInfo;
use AbstractRepo\DataModels\ModelField;
use AbstractRepo\Enums;
use AbstractRepo\Exceptions;
use AbstractRepo\Exceptions\RepositoryException;
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

/**
 * This abstract class allows to extend a custom repository layer and, by choosing a model, to have the basic
 * CRUD functionalities already implemented.
 */
abstract class AbstractRepository implements Interfaces\IRepository
{
    /**
     * The class of the model handled by the repository (ex: AbstractRepo\Models\Book)
     * @var string|mixed
     */
    private string $modelClassPathName;

    /**
     * The name of the database table of the handled model.
     * @var string|mixed
     */
    private string $tableName;

    /**
     * @var ModelHandler
     */
    private ModelHandler $modelHandler;

    /**
     * {@inheritDoc}
     * @return string
     */
    abstract static public function getModel(): string;

    /**
     * @param PDO $pdo
     *
     * @throws Exceptions\RepositoryException
     */
    function __construct(
        protected PDO $pdo
    )
    {
        try {
            /**
             * Invoke the method to get the model handled by the repository (ex: Book).
             */
            $this->modelClassPathName = $this->getModel();

            $modelReflectionClass = ReflectionUtility::getReflectionClass($this->modelClassPathName);

            /**
             * Throw error if the model doesn't implement {@see Interfaces\IModel}.
             */
            if (!$modelReflectionClass->implementsInterface(Interfaces\IModel::class)) {
                throw new Exceptions\RepositoryException(Exceptions\RepositoryException::MODEL_MUST_IMPLEMENTS_INTERFACE);
            }

            $this->tableName = ReflectionUtility::getTableName($modelReflectionClass);

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->modelHandler = new ModelHandler();

            $this->processModel(
                reflectionClass: $modelReflectionClass,
                modelHandler: $this->modelHandler
            );
        } catch (Exception $e) {
            throw new Exceptions\RepositoryException($e->getMessage());
        }
    }

    #region Private methods

    /**
     * Method to analyze the model given and store with the model handler the basic information of it.
     *
     * @param ReflectionClass $reflectionClass
     * @param ModelHandler    $modelHandler
     *
     * @return void
     * @throws Exceptions\ReflectionException
     * @throws RepositoryException
     * @throws ReflectionException
     */
    private function processModel(ReflectionClass $reflectionClass, ModelHandler $modelHandler): void
    {
        $modelHandler->searchableFieldsQueryBuilder
            ->select(["`{$this->tableName}`.*"])
            ->from(from: $this->tableName, alias: $this->tableName);

        $searchablePlaceholders = [];
        $searchableConditions = [];

        $reflectionProperties = $reflectionClass->getProperties();

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyType = $reflectionProperty->getType()->getName();

            /**
             * Stores whether a property is autoIncrement or not.
             * Used to determine whether it's needed to be inserted or not.
             */
            $isAutoIncrement = false;

            /**
             * Stores whether a property is required.
             */
            $isRequired = false;

            /**
             * Stores the foreign key type
             * @var Enums\Relationship|null $foreignKeyRelationshipType
             */
            $foreignKeyRelationshipType = null;

            /**
             * Stores the name of the foreign key referenced column
             */
            $foreignKeyColumnName = null;

            /**
             * Stores the type of the foreign key referenced column
             */
            $foreignKeyColumnType = null;

            /**
             * Stores whether a property is searchable by the {@see self::findByQuery()} method.
             */
            $isSearchable = false;

            /**
             * Stores whether a property is primary key.
             */
            $isPrimaryKey = false;

            /**
             * {@see FieldInfo::$allowsNull}
             */
            $allowsNull = false;

            /**
             * {@see Attributes\OneToMany::$referencedColumn}
             */
            $oneToManyReferencedField = null;

            $propertyAttributes = $reflectionProperty->getAttributes();

            foreach ($propertyAttributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $attributeName     = $attribute->getName();

                /**
                 * @var Attributes\Searchable $attributeInstance
                 */
                if ($attributeName === Attributes\Searchable::class) {
                    $isSearchable = true;
                }

                /**
                 * @var Attributes\PrimaryKey $attributeInstance
                 */
                if ($attributeName === Attributes\PrimaryKey::class) {
                    $isPrimaryKey    = true;
                    $isAutoIncrement = $attributeInstance->autoIncrement;
                }

                /**
                 * @var ForeignKey $attributeInstance
                 */
                if ($attributeInstance instanceof ForeignKey) {
                    $foreignKeyRelationshipType = Enums\Relationship::fromAttribute($attributeInstance);

                    if ($foreignKeyRelationshipType === Enums\Relationship::ONE_TO_MANY) {
                        /**
                         * @var Attributes\OneToMany $attributeInstance
                         */
                        if ($propertyType !== 'array') {
                            throw new RepositoryException(
                                RepositoryException::ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE
                            );
                        }

                        $propertyType             = $attributeInstance->referencedClass;
                        $oneToManyReferencedField = $attributeInstance->referencedColumn;
                    } else {
                        /**
                         * @var Attributes\ManyToOne|Attributes\OneToOne $attributeInstance
                         */
                        $foreignKeyColumnName = $attributeInstance->columnName;
                    }

                    $primaryKeyProperty   = ReflectionUtility::getPrimaryKeyProperty($propertyType);
                    $foreignKeyColumnType = $primaryKeyProperty->getType()->getName();
                }
            }

            /**
             * This check had to be made due to a bug/bad documentation of the method {@see ReflectionProperty::hasDefaultValue()}
             * that doesn't work with promoted properties.
             */
            if (!$reflectionProperty->isPromoted()) {
                /**
                 * If it doesn't have a default value and is not a key identity then it's required
                 */
                $isRequired = !$reflectionProperty->hasDefaultValue() && !$isAutoIncrement;
                $allowsNull = $reflectionProperty->getType()->allowsNull();
            } else {
                /**
                 * If it's a promoted property check the default value in the constructor by getting the reflection parameter
                 */
                $constructorParameter = ReflectionUtility::getConstructorParameter(
                    reflectionClass: $reflectionClass,
                    parameterName: $propertyName
                );

                /**
                 * This cannot happen since a property if it's promoted must be in the constructor.
                 */
                // @codeCoverageIgnoreStart
                if (!$constructorParameter) {
                    throw new Exceptions\RepositoryException(Exceptions\RepositoryException::INVALID_PROMOTED_PROPERTY);
                }
                // @codeCoverageIgnoreEnd

                /**
                 * If there's no default value in the promoted property, and it's not auto increment then it's required.
                 */
                $isRequired = !$constructorParameter->isDefaultValueAvailable() && !$isAutoIncrement;

                $allowsNull = $constructorParameter->allowsNull();
            }

            if ($foreignKeyRelationshipType === Enums\Relationship::ONE_TO_MANY) {
                if ($isRequired || !$reflectionProperty->getType()->allowsNull()) {
                    throw new RepositoryException(
                        RepositoryException::ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE
                    );
                }

                if ($isPrimaryKey) {
                    throw new RepositoryException(
                        RepositoryException::ONE_TO_MANY_CANNOT_BE_PRIMARY_KEY
                    );
                }
            }

            $modelHandler->save(
                fieldName: $propertyName,
                fieldInfo: new FieldInfo(
                    propertyName: $propertyName,
                    propertyType: $propertyType,
                    isRequired: $isRequired,
                    allowsNull: $allowsNull,
                    isPrimaryKey: $isPrimaryKey,
                    autoIncrement: $isAutoIncrement,
                    isForeignKey: $foreignKeyRelationshipType !== null,
                    defaultValue: $reflectionProperty->getDefaultValue(),
                    foreignKeyRelationshipType: $foreignKeyRelationshipType,
                    foreignKeyColumnName: $foreignKeyColumnName,
                    foreignKeyColumnType: $foreignKeyColumnType,
                    oneToManyReferencedField: $oneToManyReferencedField
                )
            );

            if ($isSearchable) {
                if ($foreignKeyRelationshipType !== null) {
                    if ($foreignKeyRelationshipType !== Enums\Relationship::ONE_TO_MANY) {
                        $foreignKeyTableName = ReflectionUtility::getTableName($propertyType);
                        $foreignKeyAlias = $foreignKeyTableName . $propertyName;
                        $foreignKeyPrimaryKeyColumnName = ReflectionUtility::getPrimaryKeyColumnName($propertyType);

                        $joinCondition = "`{$foreignKeyTableName}` AS {$foreignKeyAlias} 
                                               ON {$this->tableName}.{$foreignKeyColumnName} = {$foreignKeyAlias}.{$foreignKeyPrimaryKeyColumnName}";

                        if ($isRequired && !$allowsNull) {
                            $modelHandler->searchableFieldsQueryBuilder
                                ->innerJoin($joinCondition);
                        } else {
                            $modelHandler->searchableFieldsQueryBuilder
                                ->leftJoin($joinCondition);
                        }

                        $foreignKeySearchableFields = $this->getModelSearchableSimpleFields($propertyType);

                        if (!$foreignKeySearchableFields) {
                            continue;
                        }

                        foreach ($foreignKeySearchableFields AS $searchableField) {
                            $placeHolder = $foreignKeyAlias . $searchableField;

                            $searchableConditions[] = "{$foreignKeyAlias}.{$searchableField} LIKE :{$placeHolder}";
                            $searchablePlaceholders[] = $placeHolder;
                        }
                    }
                } else {
                    $placeHolder = "{$this->tableName}{$propertyName}";

                    $searchableConditions[] = "`{$this->tableName}`.{$propertyName} LIKE :{$placeHolder}";
                    $searchablePlaceholders[] = $placeHolder;
                }
            }
        }

        $modelHandler->searchableFieldsQueryBuilder
            ->where("(" . implode(" OR ", $searchableConditions) . ")")
            ->addPlaceholders($searchablePlaceholders);
    }

    /**
     * Recursive function to retrieve a property value key value.
     *
     * @param IModel $model
     * @param string $fieldName
     *
     * @return mixed
     * @throws ReflectionException
     * @throws Exceptions\ReflectionException
     * @throws RepositoryException
     */
    private function getPropertyValueRecursive(
        IModel $model,
        string $fieldName
    ): mixed
    {
        $value = $model->$fieldName;

        /**
         * If it's not a nested object then return the value (primitive type)
         */
        if (!is_object($value)) {
            return $value;
        }

        if (!($value instanceof IModel)) {
            throw new Exceptions\RepositoryException(RepositoryException::MODEL_IS_NOT_HANDLED);
        }

        /**
         * Get reflection class of the related object
         */
        $reflectionClassObject = new ReflectionClass($value);

        /**
         * Get primary key and primary key field name
         */
        $primaryKeyField     = ReflectionUtility::getPrimaryKeyProperty($reflectionClassObject);
        $primaryKeyFieldName = $primaryKeyField->getName();

        /**
         * Get table name of the related object
         */
        $tableName = ReflectionUtility::getTableName($reflectionClassObject);

        /**
         * Get primary key value (this can be a nested object as well, so we need to call the recursive function)
         */
        $value = $this->getPropertyValueRecursive($value, $primaryKeyFieldName);

        /**
         * Find the related object to ensure that there are no orphan data.
         */
        $object = $this->findById($value, $reflectionClassObject->getName(), $tableName);

        if (!$object) {
            throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);
        }

        /**
         * Return the value of the property
         */
        return $value;
    }

    /**
     * Validates the model sent in the request.
     *
     * @param IModel $model
     *
     * @return void
     * @throws Exceptions\RepositoryException
     */
    private function validateRequest(Interfaces\IModel $model): void
    {
        if (get_class($model) !== $this->modelClassPathName) {
            throw new Exceptions\RepositoryException(RepositoryException::MODEL_IS_NOT_HANDLED);
        }
    }

    /**
     * Returns the PDOStatement for the insert operation.
     *
     * @param IModel $model
     *
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws Exceptions\RepositoryException
     */
    private function getInsertStatement(Interfaces\IModel $model): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        /**
         * Retrieves all the data from the given model.
         */
        $modelData = $this->getModelData($model);

        /**
         * Get an array with just the columns names
         */
        $columns = array_map(fn(ModelField $val) => $val->fieldName, $modelData);

        /**
         * Create the insert statement with the bind params
         * E.g.
         * INSERT INTO t1 (col) VALUES (:col)
         */
        $queryBuilder->insert($this->tableName, $columns);

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        /**
         * Bind the data to the placeholders.
         */
        return $this->bindValues($modelData, $stmt);
    }

    /**
     * Returns the PDOStatement for the update operation.
     *
     * @param IModel $model
     *
     * @return PDOStatement
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     * @throws Exceptions\RepositoryException
     */
    private function getUpdateStatement(Interfaces\IModel $model): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        /**
         * Retrieves all the data from the given model.
         */
        $modelData = $this->getModelData($model);

        $keyProp = $this->modelHandler->getKey();

        /**
         * @var string $keyPropName Is used to identify the primary key later in the foreach
         */
        if ($keyProp->isForeignKey) {
            $keyPropName = $keyProp->foreignKeyColumnName;
        } else {
            $keyPropName = $keyProp->propertyName;
        }

        /**
         * Get the update string (ex: col1 = :col1, col2 = :col2) without the primary key since it can't be updated.
         */
        $nonPkColumns = [];

        $keyPropValue = null;

        foreach ($modelData as $field) {
            if ($field->fieldName !== $keyPropName) {
                $nonPkColumns[] = $field->fieldName;
            } else {
                $keyPropValue = $field->fieldValue;
            }
        }

        $queryBuilder->update($this->tableName, $nonPkColumns);

        $queryBuilder->where("{$keyPropName} = " . QueryBuilder::BIND_CHAR . "{$keyPropName}");

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        /**
         * Bind values to the statement and also for the where id = :id
         */
        $stmt = $this->bindValues($modelData, $stmt);

        $stmt->bindParam($keyPropName, $keyPropValue, PDOUtil::getPDOType(gettype($keyPropValue)));

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the delete operation.
     *
     * @param $id
     *
     * @return PDOStatement
     */
    private function getDeleteStatement($id): PDOStatement
    {
        $queryBuilder = new QueryBuilder();

        $keyProp = $this->modelHandler->getKey();

        /**
         * If it's a fk use the foreign key column name
         */
        if ($keyProp->isForeignKey) {
            $keyPropName = $keyProp->foreignKeyColumnName;
        } else {
            $keyPropName = $keyProp->propertyName;
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
     * Returns an array used in the insert an update operation to get every value of the object.
     *
     * @param Interfaces\IModel $model
     *
     * @return ModelField[]
     * @throws Exceptions\ReflectionException
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     */
    private function getModelData(Interfaces\IModel $model): array
    {
        $values = [];

        foreach ($this->modelHandler->get() as $property) {
            if ($property->foreignKeyRelationshipType === Enums\Relationship::ONE_TO_MANY) {
                continue;
            }

            $propertyName = $property->propertyName;
            $propertyType = $property->propertyType;

            if ($property->isForeignKey) {

                if ($property->foreignKeyRelationshipType === Enums\Relationship::MANY_TO_ONE
                    || $property->foreignKeyRelationshipType === Enums\Relationship::ONE_TO_ONE) {

                    /**
                     * Recursively checks in the nested foreign key objects for the value.
                     * E.g.
                     * The primary key of T1 is T2, and the primary key of T2 is T3.
                     * Then to get the value of the foreign key T2 in T1 we need to fetch the value of the primary key of T3.
                     */
                    $value = $this->getPropertyValueRecursive($model, $propertyName);

                    $propertyName = $property->foreignKeyColumnName;

                    /**
                     * If it's ONE_TO_ONE there shouldn't be other records with the same foreign key
                     */
                    if ($property->foreignKeyRelationshipType === Enums\Relationship::ONE_TO_ONE) {
                        $keyProperty = $this->modelHandler->getKey();

                        $keyPropertyFieldName = $keyProperty->isForeignKey ? $keyProperty->foreignKeyColumnName : $keyProperty->propertyName;

                        /**
                         * If the primary key property is the same as the current foreign key it doesn't make sense to check
                         * for duplicates since the primary key already acts as a unique constraint.
                         */
                        if ($keyPropertyFieldName !== $propertyName) {
                            $recordWithSameForeignKey = $this->findFirst(new FetchParams(
                                conditions: "{$propertyName} = :foreignKeyValue AND {$keyPropertyFieldName} <> :keyProperty",
                                bind: [
                                    "foreignKeyValue" => $value,
                                    "keyProperty"     => $model->$keyPropertyFieldName
                                ]
                            ));

                            if ($recordWithSameForeignKey) {
                                throw new RepositoryException(RepositoryException::ONE_TO_ONE_RELATIONSHIP_FAIL);
                            }
                        }
                    }

                    $propertyType = gettype($value);
                }

            } else {
                // If it's not a fk just add the value
                $value = $model->$propertyName ?? $property->defaultValue ?? null;
            }

            /**
             * This cannot be possible since it would be blocked by the instantiation of the object itself.
             */
            // @codeCoverageIgnoreStart
            if ($property->isRequired && empty($value) && !$property->allowsNull) {
                throw new Exceptions\RepositoryException("{$propertyName} is required!");
            }
            // @codeCoverageIgnoreEnd

            // Array to store all the information to create the insert
            $values[] = new ModelField(
                fieldName: $propertyName,
                fieldType: $propertyType,
                fieldValue: $value ?? null
            );

        }

        return $values;
    }

    /**
     * Make a filtered select with the parameter passed
     *
     * @param string $tableName
     * @param string $modelClass
     * @param string $property
     * @param mixed  $value
     *
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
        if ($modelClass !== $this->modelClassPathName) {
            $property = ReflectionUtility::getProperty($modelClass, $property);

            $foreignKeyAttribute = ReflectionUtility::getAttribute($property, ForeignKey::class);

            if ($foreignKeyAttribute !== null) {
                $fkAttributeInstance = $foreignKeyAttribute->newInstance();

                /**
                 * This can never happen because this method is just called by {@see AbstractRepository::findById()}
                 * and a class cannot have as primary key a field with a ONE TO MANY relationship
                 * {@see RepositoryException::ONE_TO_MANY_CANNOT_BE_PRIMARY_KEY}
                 */
                // @codeCoverageIgnoreStart
                if ($fkAttributeInstance instanceof Attributes\OneToMany) {
                    throw new RepositoryException(RepositoryException::CANNOT_FIND_WHERE_BY_ONE_TO_MANY_FIELD);
                }
                // @codeCoverageIgnoreEnd

                /**
                 * @var Attributes\OneToOne|Attributes\ManyToOne $fkAttributeInstance
                 */

                $propertyName = $fkAttributeInstance->columnName;

                $keyProperty  = ReflectionUtility::getPrimaryKeyProperty($modelClass);
                $propertyType = PDOUtil::getPDOType($keyProperty->getType()->getName());
            } else {
                $propertyName = $property->getName();
                $propertyType = PDOUtil::getPDOType($property->getType()->getName());
            }
        } else {
            $property = $this->modelHandler->get($property);

            if ($property->isForeignKey) {
                $propertyName = $property->foreignKeyColumnName;
                $propertyType = PDOUtil::getPDOType($property->foreignKeyColumnType);
            } else {
                $propertyName = $property->propertyName;
                $propertyType = PDOUtil::getPDOType($property->propertyType);
            }
        }

        $queryBuilder->where("{$propertyName} = " . QueryBuilder::BIND_CHAR . "{$propertyName}");

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
     * Bind the given values to the given statement.
     *
     * @param ModelField[] $values
     * @param PDOStatement $stmt
     *
     * @return PDOStatement
     */
    private function bindValues(array $values, PDOStatement $stmt): PDOStatement
    {
        foreach ($values as $value) {
            if ($value->fieldValue === null) {
                $type = PDOUtil::NULL_TYPE;
            } else {
                $type = PDOUtil::getPDOType($value->fieldType);
            }

            $placeholder = QueryBuilder::BIND_CHAR . $value->fieldName;
            $value       = $value->fieldValue;

            $stmt->bindValue($placeholder, $value, $type);
        }
        return $stmt;
    }

    /**
     * Returns the total amount of items of a given query.
     *
     * @param string           $subquery
     * @param FetchParams|null $params
     *
     * @return int
     */
    private function getItemsCount(string $subquery, ?FetchParams $params): int
    {
        $query = (new QueryBuilder())
            ->select(["COUNT(*) as itemsCount"])
            ->from(from: "({$subquery})", alias: "subquery", useBacktick: false)
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

    /**
     * Bind the given params from the {@see FetchParams} to the given statement.
     *
     * @param PDOStatement     $stmt
     * @param FetchParams|null $params
     *
     * @return void
     */
    private function bindParams(PDOStatement $stmt, ?FetchParams $params): void
    {
        foreach ($params?->getBind() ?? [] as $key => $value) {
            $type = gettype($value);
            if ($type === 'array') {
                foreach ($value as $index => $val) {
                    $stmt->bindValue($key . $index, $val);
                }
            } else {
                $stmt->bindValue($key, $value, PDOUtil::getPDOType(gettype($value)));
            }
        }
    }

    /**
     * Simple raw search method.
     *
     * @param array       $columns
     * @param string      $table
     * @param string|null $conditions
     * @param array|null  $params
     *
     * @return array
     */
    private function select(
        array   $columns,
        string  $table,
        ?string $conditions = null,
        ?array  $params = null
    ): array
    {
        $queryBuilder = (new QueryBuilder())
            ->select($columns)
            ->from($table);

        if ($conditions) {
            $queryBuilder
                ->where($conditions);
        }

        $stmt = $this->pdo->prepare($queryBuilder->getQuery());

        if ($params) {
            $this->bindParams($stmt, new FetchParams(bind: $params));
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all the simple searchable fields without nested searching.
     *
     * @param string $class
     *
     * @return array
     * @throws Exceptions\ReflectionException
     * @throws ReflectionException
     */
    private function getModelSearchableSimpleFields(string $class): array
    {
        $searchableProperties = ReflectionUtility::getPropertyWithAttribute($class, Attributes\Searchable::class);

        return array_map(
            fn(\ReflectionProperty $property) => ReflectionUtility::getColumnName($class, $property->name),
            $searchableProperties
        );
    }

    /**
     * Returns the default ordering
     * @return string[]
     * @throws RepositoryException
     */
    private function getDefaultOrderBy(): array
    {
        $firstProperty = array_values($this->modelHandler->get())[0];
        $columnName = $firstProperty->foreignKeyColumnName ?? $firstProperty->propertyName;

        return ["{$columnName} " . QueryBuilder::ORDER_ASC];
    }

    /**
     * Process the given params to the given query builder
     * @param FetchParams  $params
     *
     * @return string
     */
    private function getWhereConditions(FetchParams $params): string
    {
        $conditions = $params->getConditions();

        foreach (QueryBuilder::findArrayBinds($conditions) as $arrayBind) {
            /**
             * If this condition is true it means that there are no binds for the
             * found array placeholder
             */
            if (!isset($params->getBind()[$arrayBind[1]])) {
                continue;
            }

            $bindValues = $params->getBind()[$arrayBind[1]];

            $placeholders = [];

            for ($i = 0; $i < count($bindValues); $i++) {
                $placeholders[] = QueryBuilder::BIND_CHAR . $arrayBind[1] . $i;
            }

            $conditions = str_replace($arrayBind[0], implode(',', $placeholders), $conditions);
        }

        return $conditions;
    }

    #endregion

    #region Public methods

    /**
     * Returns the instance model from the array received by the database.
     *
     * @param mixed  $obj
     * @param string $modelClass
     *
     * @return Interfaces\IModel|null
     * @throws Exceptions\RepositoryException
     * @throws ReflectionException
     * @throws Exceptions\ReflectionException
     */
    public function getMappedObject(mixed $obj, string $modelClass): ?Interfaces\IModel
    {
        if (!isset($obj) || !$obj) {
            return null;
        }

        $primaryKeyValue = $obj[ReflectionUtility::getPrimaryKeyColumnName($modelClass)]
            ?? throw new RepositoryException(RepositoryException::CANNOT_FIND_PRIMARY_KEY_VALUE);

        $foreignKeyProperties = ReflectionUtility::getForeignKeyProperties(class: $modelClass);

        foreach ($foreignKeyProperties as $foreignKeyProperty) {
            $foreignKeyAttribute = ReflectionUtility::getAttribute($foreignKeyProperty, ForeignKey::class)->newInstance();

            if ($foreignKeyAttribute instanceof Attributes\ManyToOne
                || $foreignKeyAttribute instanceof Attributes\OneToOne
            ) {
                $foreignKeyClass          = $foreignKeyProperty->getType()->getName();
                $foreignKeyReflectedClass = ReflectionUtility::getReflectionClass(class: $foreignKeyProperty->getType()->getName());
                $foreignKeyTableName      = ReflectionUtility::getTableName(class: $foreignKeyReflectedClass);

                /**
                 * @var Attributes\ManyToOne|Attributes\OneToOne $foreignKeyAttribute
                 */
                $columnName = $foreignKeyAttribute->columnName;

                // Removes the id from the object
                $id = $obj[$columnName];
                unset($obj[$columnName]);

                if ($id === null) {
                    $foreignKeyObject = null;
                } else {
                    $foreignKeyObject = $this->findById(
                        id: $id,
                        class: $foreignKeyClass,
                        table: $foreignKeyTableName
                    );

                    if (is_null($foreignKeyObject)) {
                        throw new Exceptions\RepositoryException(Exceptions\RepositoryException::RELATED_OBJECT_NOT_FOUND);
                    }
                }
            } else {
                $foreignKeyTableName        = ReflectionUtility::getTableName($foreignKeyAttribute->referencedClass);
                $foreignKeyPrimaryKeyColumn = ReflectionUtility::getPrimaryKeyColumnName($foreignKeyAttribute->referencedClass);

                $foreignKeyObject = $this->select(
                    columns: [$foreignKeyPrimaryKeyColumn],
                    table: $foreignKeyTableName,
                    conditions: "{$foreignKeyAttribute->referencedColumn} = :id",
                    params: ["id" => $primaryKeyValue]
                );

            }

            $obj[$foreignKeyProperty->getName()] = $foreignKeyObject;
        }

        try {
            $mappedObj = ORM::getNewInstance($modelClass, (array)$obj);
        } catch (Exceptions\ORMException $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }

        return $mappedObj;
    }

    /**
     * Find all the objects filtered by the given params if any.
     *
     * @param FetchParams|null $params
     *
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
                $queryBuilder->where($this->getWhereConditions($params));
            }

            $orderBy = $params?->getOrderBy() ?? $this->getDefaultOrderBy();

            $queryBuilder->orderBy($orderBy);

            $queryNonPaginated = $queryBuilder->getQuery();

            if ($isPaginated) {
                $queryBuilder->paginate($params->getPage(), $params->getItemsPerPage());
            }

            $stmt = $this->pdo->prepare($queryBuilder->getQuery());

            $this->bindParams($stmt, $params);

            $stmt->execute();

            $result    = $stmt->fetchAll(PDO::FETCH_CLASS);
            $mappedArr = [];

            foreach ($result as $item) {
                $mappedArr[] = $this->getMappedObject((array)$item, $this->modelClassPathName);
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
     * Find all the records that matches the given query.
     *
     * @param mixed            $query
     * @param FetchParams|null $params
     *
     * @return FetchedData|array
     * @throws RepositoryException
     */
    public function findByQuery(mixed $query, ?FetchParams $params = null): FetchedData|array
    {
        try {
            $queryBuilder = clone $this->modelHandler->searchableFieldsQueryBuilder;
            $placeholders = $this->modelHandler->searchableFieldsQueryBuilder->getPlaceholders();

            if (empty($placeholders)) {
                return [];
            }

            $query = '%' . $query . '%';

            $binds = [];

            foreach ($placeholders AS $placeholder) {
                $binds[$placeholder] = $query;
            }

            if ($params?->getConditions()) {
                $queryBuilder->where("AND " . $this->getWhereConditions($params));
            }

            $binds = array_merge($binds, $params?->getBind() ?? []);

            $params = new FetchParams(page: $params?->getPage(), itemsPerPage: $params?->getItemsPerPage(), bind: $binds, orderBy: $params?->getOrderBy());

            $isPaginated = $params->getPage() !== null && $params->getItemsPerPage() !== null;

            $orderBy = $params?->getOrderBy() ?? $this->getDefaultOrderBy();

            $queryBuilder->orderBy($orderBy);

            $queryNonPaginated = $queryBuilder->getQuery();

            if ($isPaginated) {
                $queryBuilder->paginate($params->getPage(), $params->getItemsPerPage());
            }

            $stmt = $this->pdo->prepare($queryBuilder->getQuery());

            $this->bindParams($stmt, $params);

            $stmt->execute();

            $result    = $stmt->fetchAll(PDO::FETCH_CLASS);
            $mappedArr = [];

            foreach ($result as $item) {
                $mappedArr[] = $this->getMappedObject((array)$item, $this->modelClassPathName);
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
     * Returns the first record for the given params
     *
     * @param FetchParams|null $params
     *
     * @return IModel|null
     * @throws Exceptions\RepositoryException
     */
    public function findFirst(?FetchParams $params = null): IModel|null
    {
        if (!$params) {
            $params = new FetchParams();
        }

        $params->setPage(0);
        $params->setItemsPerPage(1);

        $data = $this->find($params)->getData();

        if (empty($data)) {
            return null;
        }

        return $data[0];
    }

    /**
     * Returns the first record found by id
     *
     * @param             $id
     * @param string|null $class
     * @param string|null $table
     *
     * @return IModel|null
     * @throws Exceptions\RepositoryException If it finds multiple results, meaning database or entities are not
     *                                        configured properly
     */
    public function findById($id, string $class = null, string $table = null): ?Interfaces\IModel
    {
        try {
            /**
             * Since this function will be called recursively to handle nesting and foreign Attributes\Keys,
             * it could be called with different modelClasses and tableNames
             */
            $modelClass = $class ?? $this->modelClassPathName;
            $tableName  = $table ?? $this->tableName;

            $keyProperty = ReflectionUtility::getPrimaryKeyProperty($modelClass);

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
     * Save the given model
     *
     * @param Interfaces\IModel $model
     *
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
            $key = ReflectionUtility::getPrimaryKeyProperty($this->modelClassPathName);

            $keyField = $this->modelHandler->getKey();

            if ($keyField->autoIncrement) {
                $key->setValue($model, $this->pdo->lastInsertId());
            }
        } catch (Exception $ex) {
            throw new Exceptions\RepositoryException($ex->getMessage());
        }
    }

    /**
     * Updates the given model
     *
     * @param Interfaces\IModel $model
     *
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
     * Deletes the model by the given id
     *
     * @param $id
     *
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