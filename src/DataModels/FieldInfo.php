<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

use AbstractRepo\Enums;

/**
 * Used to handle model fields
 */
final class FieldInfo
{
    public function __construct(
        public string              $fieldName,
        public ?string             $fieldType,
        public bool                $isRequired,
        public bool                $isKey,
        public bool                $isIdentity,
        public bool                $isFk,
        public mixed               $defaultValue = null,
        public ?Enums\Relationship $relationshipType = null,
        public ?string             $fkColumnName = null,
        public ?string             $fkColumnType = null
    )
    {
    }
}
