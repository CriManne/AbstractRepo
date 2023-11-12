<?php
declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
/**
 * Identifies a class as a relational entity
 */
final readonly class Entity
{
    public function __construct(
        public ?string $tableName = null
    )
    {
    }
}