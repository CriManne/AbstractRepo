<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Attribute that identifies a class as a database entity.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Entity
{
    public function __construct(
        /**
         * @var string $tableName Name of the database table related to the entity.
         */
        public string $tableName
    )
    {
    }
}