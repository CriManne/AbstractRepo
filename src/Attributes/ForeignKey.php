<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use AbstractRepo\Enums\Relationship;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a foreign key field
 */
readonly final class ForeignKey
{
    public function __construct(
        public Relationship $relation,
        public ?string      $columnName = null
    )
    {
    }
}
