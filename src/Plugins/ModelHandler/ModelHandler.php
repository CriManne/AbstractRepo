<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\ModelHandler;

use AbstractRepo\DataModels\FieldInfo;
use AbstractRepo\Exceptions;
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
     * All the fields that are searchable, so that can be included in the {@see AbstractRepository::findByQuery()} method
     * @var array
     */
    private array $searchableFields;

    /**
     * This field will be the reference to the key value of the model
     * @var FieldInfo $fieldInfo
     */
    private FieldInfo $keyField;

    public function __construct()
    {
        $this->fields = array();
        $this->searchableFields = array();
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
     * Adds a searchable field
     *
     * @param string $fieldName
     * @return void
     */
    public function addSearchableField(string $fieldName): void
    {
        $this->searchableFields[] = $fieldName;
    }

    /**
     * Returns the searchable fields
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
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