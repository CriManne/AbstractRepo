<?php

declare(strict_types=1);

namespace AbstractRepo\Models;

use AbstractRepo\Models;
use AbstractRepo\Exceptions;

final class ModelHandler
{
    /**
     * @var array[string]FieldInfo $fields
     */
    private array $fields;
    public function __construct(
    )
    {
        $this->fields = array();
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
}