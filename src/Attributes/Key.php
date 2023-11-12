<?php

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Identifies a (primary) key property of an entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a (primary) key property of an entity
 */
readonly final class Key
{
    public function __construct(
        public bool $identity = false
    )
    {
    }
}