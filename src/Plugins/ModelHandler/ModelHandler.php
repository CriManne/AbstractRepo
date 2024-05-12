<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\ModelHandler;

use AbstractRepo\DataModels\FieldInfo;
use AbstractRepo\DataModels\SearchableField;
use AbstractRepo\Exceptions;
use AbstractRepo\Plugins\QueryBuilder\QueryBuilder;
use AbstractRepo\Repository\AbstractRepository;

/**
 * The class will handle all the fields of the model the repository handles.
 */
final class ModelHandler
{
    /**
     * All the fields handled
     * @var array[string]FieldInfo $fields
     */
    private array $fields;

    /**
     * The query builder to search the table by query {@see AbstractRepository::findByQuery()}
     * @var QueryBuilder $searchableFieldsQueryBuilder
     */
    public QueryBuilder $searchableFieldsQueryBuilder;

    /**
     * This field will be the reference to the key value of the model
     * @var FieldInfo $fieldInfo
     */
    private FieldInfo $keyField;

    public function __construct()
    {
        $this->fields = array();
        $this->searchableFieldsQueryBuilder = new QueryBuilder();
    }

    /**
     * Adds a field to the list
     *
     * @param string $fieldName
     * @param FieldInfo $fieldInfo
     * @return void
     */
    public function save(string $fieldName, FieldInfo $fieldInfo): void
    {
        $this->fields[$fieldName] = $fieldInfo;

        if ($fieldInfo->isPrimaryKey) {
            $this->keyField = &$this->fields[$fieldName];
        }
    }

    /**
     * Returns the requested field
     *
     * @param ?string $fieldName
     * @return FieldInfo|FieldInfo[]
     * @throws Exceptions\RepositoryException
     */
    public function get(?string $fieldName = null): FieldInfo|array
    {
        if (!$fieldName) {
            return $this->fields;
        }

        return $this->fields[$fieldName] ?? throw new Exceptions\RepositoryException("Field {$fieldName} not found");
    }

    /**
     * Return the reference to the key value
     *
     * @return FieldInfo
     */
    public function getKey(): FieldInfo
    {
        return $this->keyField;
    }
}