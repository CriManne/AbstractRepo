<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Identifies a (primary) key property of an entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class Key
{
    public const isIdentityMethod = 'isIdentity';

    public function __construct(
        public bool $identity = false
    )
    {
    }

    public function isIdentity(): bool
    {
        return $this->identity;
    }
}