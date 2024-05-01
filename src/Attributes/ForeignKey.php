<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use AbstractRepo\Enums\Relationship;
use Attribute;

/**
 * Attribute that identifies a property as a foreign key.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class ForeignKey
{
    public function __construct(
        /**
         * @var Relationship $relationship The foreign key type, {@see Relationship}.
         */
        public Relationship $relationship,

        /**
         * @var string $columnName The column name in the related entity.
         */
        public string       $columnName
    )
    {
    }
}
