<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Relationship type ONE TO ONE
 * {@inheritDoc}
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class OneToOne extends ForeignKey
{
    public function __construct(
        /**
         * @var string $columnName The column name in the related entity.
         */
        public string $columnName,
    )
    {
    }
}
