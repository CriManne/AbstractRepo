<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use AbstractRepo\Enums\Relationship;
use Attribute;

/**
 * Identifies a foreign key field
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class ForeignKey
{
    public const getRelationshipMethod = 'getRelationship';
    public const getColumnNameMethod = 'getColumnName';

    public function __construct(
        public Relationship $relationship,
        public ?string      $columnName = null
    )
    {
    }

    public function getRelationship(): Relationship
    {
        return $this->relationship;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }
}
