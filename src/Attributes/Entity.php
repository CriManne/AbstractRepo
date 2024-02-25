<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Identifies a class as a relational entity
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Entity
{
    public const getTableNameMethod = 'getTableName';

    public function __construct(
        public ?string $tableName = null
    )
    {
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }
}