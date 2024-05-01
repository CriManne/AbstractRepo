<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

use AbstractRepo\Enums;

/**
 * Stores the information of a model's field.
 */
final class FieldInfo
{
    public function __construct(
        /**
         * @var string $propertyName The name of the property.
         */
        public string              $propertyName,

        /**
         * @var string|null $propertyType The type of the property.
         * This can also be a class full path in case of a foreign key.
         */
        public ?string             $propertyType,

        /**
         * @var bool $isRequired Stores whether the property is required.
         */
        public bool                $isRequired,

        /**
         * @var bool $isPrimaryKey Stores whether the property is primary key.
         */
        public bool                $isPrimaryKey,

        /**
         * @var bool $autoIncrement Stores whether the property is auto increment.
         */
        public bool                $autoIncrement,

        /**
         * @var bool $isForeignKey Stores whether the property is a foreign key.
         */
        public bool                $isForeignKey,

        /**
         * @var mixed|null $defaultValue Stores the default value of the property if there's one.
         */
        public mixed               $defaultValue = null,

        /**
         * @var Enums\Relationship|null $foreignKeyRelationshipType Stores the foreign key relationship type.
         */
        public ?Enums\Relationship $foreignKeyRelationshipType = null,

        /**
         * @var string|null $foreignKeyColumnName Stores the foreign key column name.
         */
        public ?string             $foreignKeyColumnName = null,

        /**
         * @var string|null $foreignKeyColumnType Stores the foreign key column type.
         */
        public ?string             $foreignKeyColumnType = null
    )
    {
    }
}
