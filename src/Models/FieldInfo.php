<?php

declare(strict_types=1);

namespace AbstractRepo\Models;

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
        public bool                $isIdentity,
        public bool                $isFk,
        public mixed               $defaultValue = null,
        public ?Enums\Relationship $fkType = null,
        public ?string             $fkColumnName = null
    )
    {
    }
}
