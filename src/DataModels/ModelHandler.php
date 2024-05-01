<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

use AbstractRepo\DataModels;
use AbstractRepo\Exceptions;

final class ModelHandler
{
    /**
     * @var array[string]FieldInfo $fields
     */
    private array $fields;

    /**
     * @var array
     */
    private array $searchableFields;

    public function __construct(
    )
    {
        $this->fields = array();
        $this->searchableFields = array();
    }

    public function save(string $fieldName, FieldInfo $fieldInfo): void
    {
        $this->fields[$fieldName] = $fieldInfo;
    }

    /**
     * @param string $fieldName
     * @return FieldInfo
     * @throws Exceptions\RepositoryException
     */
    public function get(string $fieldName): FieldInfo
    {
        return $this->fields[$fieldName] ?? throw new Exceptions\RepositoryException("Field {$fieldName} not found");
    }

    public function addSearchableField(string $fieldName): void
    {
        $this->searchableFields[] = $fieldName;
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
}