<?php

declare(strict_types=1);

namespace AbstractRepo\Models;

use AbstractRepo\Models;

final class ManagedModel
{

    /**
     * @param Models\FieldInfo[] $fields
     */
    public function __construct(
        private readonly string $fullClassName,
        private readonly array  $fields = []
    )
    {
    }

    public function getFullClassName(): string
    {
        return $this->fullClassName;
    }

    public function getField(string $fieldName): Models\FieldInfo
    {
        return $this->fields[$fieldName];
    }

    /**
     * @return Models\FieldInfo[]
     */
    public function getFks(): array
    {
        return array_filter($this->fields, function (Models\FieldInfo $field) {
            return $field->isFk;
        });
    }

}