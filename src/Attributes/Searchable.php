<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Set a property as searchable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Searchable
{
}