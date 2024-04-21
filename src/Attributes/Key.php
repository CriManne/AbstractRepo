<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Attribute that identifies a property as a primary key.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class Key
{
    public function __construct(
        /**
         * Flag that indicates whether the property is auto increment or not.
         */
        public bool $autoIncrement = false
    )
    {
    }
}