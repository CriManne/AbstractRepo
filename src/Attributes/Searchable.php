<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use AbstractRepo\Repository\AbstractRepository;
use Attribute;

/**
 * Attribute that identifies a property as searchable by the {@see AbstractRepository::findByQuery()} method.
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Searchable
{
}